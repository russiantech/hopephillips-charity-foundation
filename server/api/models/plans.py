from api.extensions import db
from datetime import datetime


class Plan(db.Model):

    __tablename__ = "plans"

    id = db.Column(db.Integer, primary_key=True)

    code = db.Column(db.String(120), unique=True)

    interval = db.Column(db.String(20))

    amount_ngn = db.Column(db.Integer)

    created_at = db.Column(db.DateTime, default=datetime.utcnow)