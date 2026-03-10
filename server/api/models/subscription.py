from api.extensions import db
from datetime import datetime


class Subscription(db.Model):

    __tablename__ = "subscriptions"

    id = db.Column(db.Integer, primary_key=True)

    email = db.Column(db.String(255))

    plan_code = db.Column(db.String(120))

    subscription_code = db.Column(db.String(120))

    customer_code = db.Column(db.String(120))

    interval = db.Column(db.String(20))

    status = db.Column(db.String(20), default="active")

    created_at = db.Column(db.DateTime, default=datetime.utcnow)

