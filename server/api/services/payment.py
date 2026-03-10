import uuid
from flask import current_app

from api.extensions import db
from api.models.transaction import Transaction
from api.services.paystack import PaystackService
from api.services.subscription import SubscriptionService


class PaymentService:


    @staticmethod
    def usd_to_ngn(amount):

        rate = current_app.config["USD_TO_NGN_RATE"]

        return int(float(amount) * rate)


    @staticmethod
    def create_transaction(email, amount_ngn, interval, description):

        reference = str(uuid.uuid4())

        tx = Transaction(
            reference=reference,
            email=email,
            amount_ngn=amount_ngn,
            interval=interval,
            description=description
        )

        db.session.add(tx)
        db.session.commit()

        return tx


    @staticmethod
    def initialize(email, amount_usd, interval, description):

        amount_ngn = PaymentService.usd_to_ngn(amount_usd)

        plan_code = None

        if interval:
            plan_code = SubscriptionService.get_or_create_plan(
                amount_ngn,
                interval
            )

        tx = PaymentService.create_transaction(
            email,
            amount_ngn,
            interval,
            description
        )

        paystack_data = PaystackService.initialize_transaction(
            email,
            amount_ngn,
            tx.reference,
            plan_code
        )

        return tx, paystack_data

