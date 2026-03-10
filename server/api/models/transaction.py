# from api.extensions import db
# from datetime import datetime


# class Transaction(db.Model):

#     __tablename__ = "transactions"

#     id = db.Column(db.Integer, primary_key=True)

#     reference = db.Column(db.String(120), unique=True)

#     email = db.Column(db.String(255))

#     amount_ngn = db.Column(db.Integer)

#     currency = db.Column(db.String(10), default="NGN")

#     description = db.Column(db.Text)

#     status = db.Column(db.String(20), default="pending")

#     interval = db.Column(db.String(20))

#     created_at = db.Column(db.DateTime, default=datetime.utcnow)

#     paid_at = db.Column(db.DateTime)
    
#     def get_summary(self):
#         """Convert model to dictionary for API responses."""
#         data = {
#             'id': self.id,
#             'reference': self.reference,
#             'email': self.email,
#             'amount_ngn': self.amount_ngn,
#             'interval': self.interval,
#             'description': self.description,
#             'status': self.status,
#             # 'authorization_url': self.authorization_url,
#             'paid_at': self.paid_at.isoformat() if self.paid_at else None,
#             'created_at': self.created_at.isoformat() if self.created_at else None
#         }
        
#         return data



# v2
# api/models/transaction.py
from datetime import datetime
from api.extensions import db

class Transaction(db.Model):
    __tablename__ = 'transactions'
    
    id = db.Column(db.Integer, primary_key=True)
    reference = db.Column(db.String(100), unique=True, nullable=False)
    email = db.Column(db.String(255), nullable=False)
    amount_ngn = db.Column(db.Integer, nullable=False)
    interval = db.Column(db.String(20), nullable=True)
    description = db.Column(db.Text, nullable=True)
    status = db.Column(db.String(20), default='pending')
    
    # Paystack specific fields
    paystack_status = db.Column(db.String(50), nullable=True)  # Original Paystack status
    gateway_response = db.Column(db.String(255), nullable=True)
    channel = db.Column(db.String(50), nullable=True)
    card_type = db.Column(db.String(50), nullable=True)
    last4 = db.Column(db.String(10), nullable=True)
    bank = db.Column(db.String(100), nullable=True)
    
    # URLs and data
    authorization_url = db.Column(db.String(500), nullable=True)
    access_code = db.Column(db.String(100), nullable=True)
    paystack_data = db.Column(db.JSON, nullable=True)
    
    # Timestamps
    paid_at = db.Column(db.DateTime, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    def get_summary(self):
        """Convert model to dictionary for API responses."""
        return {
            'id': self.id,
            'reference': self.reference,
            'email': self.email,
            'amount_ngn': self.amount_ngn,
            'interval': self.interval,
            'description': self.description,
            'status': self.status,
            'paystack_status': self.paystack_status,
            'gateway_response': self.gateway_response,
            'channel': self.channel,
            'card_type': self.card_type,
            'last4': self.last4,
            'bank': self.bank,
            'authorization_url': self.authorization_url,
            'paid_at': self.paid_at.isoformat() if self.paid_at else None,
            'created_at': self.created_at.isoformat() if self.created_at else None
        }
    
