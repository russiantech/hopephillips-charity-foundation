from api.extensions import db
from datetime import datetime

class Customer(db.Model):

    __tablename__ = "customers"

    id = db.Column(db.Integer, primary_key=True)

    email = db.Column(db.String(255), unique=True, nullable=False)

    paystack_customer_code = db.Column(db.String(120))

    created_at = db.Column(db.DateTime, default=datetime.utcnow)

