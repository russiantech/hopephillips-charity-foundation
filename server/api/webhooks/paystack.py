import hmac
import hashlib

from flask import Blueprint, request, abort
from flask import current_app

from api.extensions import db
from api.models.transaction import Transaction
from api.models.subscription import Subscription

webhook_bp = Blueprint("webhooks", __name__)


@webhook_bp.post("/webhook/paystack")
def paystack_webhook():

    signature = request.headers.get("x-paystack-signature")

    payload = request.data

    computed = hmac.new(
        current_app.config["PAYSTACK_SECRET_KEY"].encode(),
        payload,
        hashlib.sha512
    ).hexdigest()

    if computed != signature:
        abort(401)

    event = request.json

    event_type = event["event"]

    data = event["data"]

    if event_type == "charge.success":

        reference = data["reference"]

        tx = Transaction.query.filter_by(reference=reference).first()

        if tx and tx.status != "success":

            tx.status = "success"

            db.session.commit()


    if event_type == "subscription.create":

        sub = Subscription(
            email=data["customer"]["email"],
            subscription_code=data["subscription_code"],
            code=data["plan"]["plan_code"],
            customer_code=data["customer"]["customer_code"],
            interval=data["plan"]["interval"]
        )

        db.session.add(sub)
        db.session.commit()


    if event_type == "invoice.payment_failed":

        # Handle failed subscription payment
        pass


    return "ok"

