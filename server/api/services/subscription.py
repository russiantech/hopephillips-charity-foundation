from api.extensions import db
from api.models.plans import Plan
from api.services.paystack import PaystackService


class SubscriptionService:


    @staticmethod
    def get_or_create_plan(amount_ngn, interval):

        plan = Plan.query.filter_by(
            amount_ngn=amount_ngn,
            interval=interval
        ).first()

        if plan:
            return plan.code


        paystack_plan = PaystackService.create_plan(amount_ngn, interval)

        code = paystack_plan["data"]["plan_code"]

        new_plan = Plan(
            code=code,
            amount_ngn=amount_ngn,
            interval=interval
        )

        db.session.add(new_plan)
        db.session.commit()

        return code

