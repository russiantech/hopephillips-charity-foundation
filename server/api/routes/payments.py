# from flask import Blueprint, request, jsonify
# from api.services.payment import PaymentService

# payment_bp = Blueprint("payments", __name__, url_prefix="/api/payments")


# @payment_bp.post("/initialize")
# def initialize():

#     data = request.json

#     email = data["email"]
#     amount = data["amount"]
#     interval = data.get("interval")
#     description = data.get("description")

#     tx, paystack = PaymentService.initialize(
#         email,
#         amount,
#         interval,
#         description
#     )

#     return jsonify({
#         "reference": tx.reference,
#         "amount_ngn": tx.amount_ngn,
#         "authorization_url": paystack["data"]["authorization_url"]
#     })



# @payment_bp.get("/verify/<reference>")
# def verify(reference):

#     tx = PaymentService.verify_transaction(reference)

#     return jsonify({
#         "status": tx.status
#     })





# # v2
# # api/routes/payments.py
# import hashlib
# import hmac

# from flask import Blueprint, abort, current_app, request
# import logging
# from api.services.payment import PaymentService
# from api.utils.response import api_response
# from api.services.paystack import PaystackService
# from api.models.transaction import Transaction
# from api.extensions import db

# logger = logging.getLogger(__name__)
# payment_bp = Blueprint("payments", __name__, url_prefix="/api/payments")

# @payment_bp.get("/config")
# def get_config():
#     """Public configuration endpoint - only expose non-sensitive data"""
#     return api_response(
#         success=True,
#         message="Configuration retrieved",
#         data={
#             "paystack_public_key": current_app.config['PAYSTACK_PUBLIC_KEY'],
#             "api_base_url": current_app.config['API_BASE_URL'],
#             "environment": current_app.config['ENV'],
#             "currency": "NGN",
#             "exchange_rate": current_app.config['USD_TO_NGN_RATE']
#         }
#     )
    
# @payment_bp.post("/initialize")
# def initialize():
#     """
#     Initialize a payment transaction.
#     """
#     try:
#         data = request.get_json()
#         print(data)
#         # Validate required fields
#         if not data:
#             return api_response(
#                 success=False,
#                 message="No data provided",
#                 errors={"body": "Request body is required"},
#                 status_code=400
#             )
        
#         email = data.get("email")
#         amount = data.get("amount")
#         interval = data.get("interval")
#         description = data.get("description")
        
#         # Validate email
#         if not email:
#             return api_response(
#                 success=False,
#                 message="Email is required",
#                 errors={"email": "Email field is required"},
#                 status_code=400
#             )
        
#         # Validate amount
#         if not amount:
#             return api_response(
#                 success=False,
#                 message="Amount is required",
#                 errors={"amount": "Amount field is required"},
#                 status_code=400
#             )
        
#         try:
#             amount = float(amount)
#             if amount <= 0:
#                 raise ValueError("Amount must be positive")
#         except ValueError:
#             return api_response(
#                 success=False,
#                 message="Invalid amount",
#                 errors={"amount": "Amount must be a positive number"},
#                 status_code=400
#             )
        
#         # Initialize payment
#         tx, paystack = PaymentService.initialize(
#             email=email,
#             amount_usd=amount,
#             interval=interval,
#             description=description
#         )
#         print("PAYSTACK RESPONSE:", paystack)
        
#         # # Prepare response data
#         # response_data = {
#         #     "reference": tx.reference,
#         #     "amount_ngn": tx.amount_ngn,
#         #     "amount_usd": amount,
#         #     "authorization_url": paystack["data"]["authorization_url"],
#         #     "access_code": paystack["data"].get("access_code"),
#         #     "interval": interval
#         # }
        
#         # v2
#         if not paystack.get("status"):
#             return api_response(
#                 success=False,
#                 message="Payment provider error",
#                 errors={"paystack": paystack.get("message", "Unknown error")},
#                 status_code=400
#             )

#         data = paystack.get("data", {})

#         response_data = {
#             "reference": tx.reference,
#             "amount_ngn": tx.amount_ngn,
#             "amount_usd": amount,
#             "authorization_url": data.get("authorization_url"),
#             "access_code": data.get("access_code"),
#             "interval": interval
#         }

#         return api_response(
#             success=True,
#             message="Payment initialized successfully",
#             data=response_data,
#             status_code=200
#         )
        
#     except KeyError as e:
#         logger.error(f"KeyError in payment initialization: {e}")
#         return api_response(
#             success=False,
#             message="Payment initialization failed",
#             errors={"payment": "Invalid response from payment provider"},
#             status_code=500
#         )
#     except ValueError as e:
#         logger.error(f"ValueError in payment initialization: {e}")
#         return api_response(
#             success=False,
#             message=str(e),
#             status_code=400
#         )
#     except Exception as e:
#         logger.error(f"Unexpected error in payment initialization: {e}")
#         return api_response(
#             success=False,
#             message="An unexpected error occurred",
#             errors={"server": str(e)},
#             status_code=500
#         )

# @payment_bp.get("/verify/<reference>")
# def verify(reference):
#     """
#     Verify a payment transaction.
#     """
#     try:
#         if not reference:
#             return api_response(
#                 success=False,
#                 message="Reference is required",
#                 status_code=400
#             )
        
#         # tx = PaymentService.verify_transaction(reference)
#         tx = PaystackService.verify_transaction(reference)
#         print(tx)
#         if not tx:
#             return api_response(
#                 success=False,
#                 message="Transaction not found",
#                 status_code=404
#             )
        
#         if tx["data"]["status"] == "success":

#             tx = Transaction.query.filter_by(reference=reference).first()

#             if tx and tx.status != "success":

#                 tx.status = "success"

#                 db.session.commit()
            
#             response_data = {
#                 "reference": tx.reference,
#                 "status": tx.status,
#                 "amount_ngn": tx.amount_ngn,
#                 "email": tx.email,
#                 "paid_at": tx.paid_at.isoformat() if tx.paid_at else None,
#                 "interval": tx.interval
#             }
            
#             return api_response(
#                 success=True,
#                 message="Transaction verified successfully",
#                 data=response_data,
#                 status_code=200
#             )
        
#     except Exception as e:
#         logger.error(f"Error verifying transaction {reference}: {e}")
#         return api_response(
#             success=False,
#             message="Verification failed",
#             errors={"verification": str(e)},
#             status_code=500
#         )



# # webhook verification

# @payment_bp.post("/webhook/paystack")
# def paystack_webhook():

#     signature = request.headers.get("x-paystack-signature")

#     payload = request.data

#     computed = hmac.new(
#         current_app.config["PAYSTACK_SECRET_KEY"].encode(),
#         payload,
#         hashlib.sha512
#     ).hexdigest()

#     if computed != signature:
#         abort(401)

#     event = request.json

#     if event["event"] == "charge.success":

#         reference = event["data"]["reference"]

#         tx = Transaction.query.filter_by(reference=reference).first()

#         if tx and tx.status != "success":

#             tx.status = "success"

#             db.session.commit()

#     return "ok"





# v3
# api/routes/payments.py
from datetime import datetime
import hashlib
import hmac
from flask import Blueprint, abort, current_app, request
import logging
from api.services.payment import PaymentService
from api.utils.response import api_response
from api.services.paystack import PaystackService
from api.models.transaction import Transaction
from api.extensions import db

logger = logging.getLogger(__name__)
payment_bp = Blueprint("payments", __name__, url_prefix="/api/payments")

@payment_bp.get("/config")
def get_config():
    """Public configuration endpoint - only expose non-sensitive data"""
    return api_response(
        success=True,
        message="Configuration retrieved",
        data={
            "paystack_public_key": current_app.config['PAYSTACK_PUBLIC_KEY'],
            "api_base_url": current_app.config['API_BASE_URL'],
            "environment": current_app.config['ENV'],
            "currency": "NGN",
            "exchange_rate": current_app.config['USD_TO_NGN_RATE']
        }
    )
    
@payment_bp.post("/initialize")
def initialize():
    """
    Initialize a payment transaction.
    """
    try:
        data = request.get_json()
        print(data)
        # Validate required fields
        if not data:
            return api_response(
                success=False,
                message="No data provided",
                errors={"body": "Request body is required"},
                status_code=400
            )
        
        email = data.get("email")
        amount = data.get("amount")
        interval = data.get("interval")
        description = data.get("description")
        
        # Validate email
        if not email:
            return api_response(
                success=False,
                message="Email is required",
                errors={"email": "Email field is required"},
                status_code=400
            )
        
        # Validate amount
        if not amount:
            return api_response(
                success=False,
                message="Amount is required",
                errors={"amount": "Amount field is required"},
                status_code=400
            )
        
        try:
            amount = float(amount)
            if amount <= 0:
                raise ValueError("Amount must be positive")
        except ValueError:
            return api_response(
                success=False,
                message="Invalid amount",
                errors={"amount": "Amount must be a positive number"},
                status_code=400
            )
        
        # Initialize payment
        tx, paystack = PaymentService.initialize(
            email=email,
            amount_usd=amount,
            interval=interval,
            description=description
        )
        print("PAYSTACK RESPONSE:", paystack)
        
        if not paystack.get("status"):
            return api_response(
                success=False,
                message=paystack.get("message") if paystack.get("message") else  f"Payment provider error - {paystack}",
                errors={"paystack": paystack.get("message", "Unknown error")},
                status_code=400
            )

        data = paystack.get("data", {})

        response_data = {
            "reference": tx.reference,
            "amount_ngn": tx.amount_ngn,
            "amount_usd": amount,
            "authorization_url": data.get("authorization_url"),
            "access_code": data.get("access_code"),
            "interval": interval
        }

        return api_response(
            success=True,
            message="Payment initialized successfully",
            data=response_data,
            status_code=200
        )
        
    except KeyError as e:
        logger.error(f"KeyError in payment initialization: {e}")
        return api_response(
            success=False,
            message="Payment initialization failed",
            errors={"payment": "Invalid response from payment provider"},
            status_code=500
        )
    except ValueError as e:
        logger.error(f"ValueError in payment initialization: {e}")
        return api_response(
            success=False,
            message=str(e),
            status_code=400
        )
    except Exception as e:
        logger.error(f"Unexpected error in payment initialization: {e}")
        return api_response(
            success=False,
            message="An unexpected error occurred",
            errors={"server": str(e)},
            status_code=500
        )

@payment_bp.get("/verify/<reference>")
def verify(reference):
    """
    Verify a payment transaction.
    """
    try:
        if not reference:
            return api_response(
                success=False,
                message="Reference is required",
                status_code=400
            )
        
        # Get Paystack verification data
        paystack_response = PaystackService.verify_transaction(reference)
        print("PAYSTACK VERIFY RESPONSE:", paystack_response)
        
        # Check if Paystack verification was successful
        if not paystack_response.get("status"):
            return api_response(
                success=False,
                message="Verification failed with payment provider",
                errors={"paystack": paystack_response.get("message", "Unknown error")},
                status_code=400
            )
        
        # Get the transaction from database
        tx = Transaction.query.filter_by(reference=reference).first()
        
        if not tx:
            return api_response(
                success=False,
                message="Transaction not found in database",
                status_code=404
            )
        
        # Extract payment data
        payment_data = paystack_response.get("data", {})
        payment_status = payment_data.get("status")
        
        # Update transaction based on payment status
        if payment_status == "success":
            tx.status = payment_status
            tx.paid_at = datetime.now()
            tx.paystack_data = paystack_response
            db.session.commit()
            
            response_data = {
                "reference": tx.reference,
                "status": tx.status,
                "amount_ngn": tx.amount_ngn,
                "email": tx.email,
                "paid_at": tx.paid_at.isoformat() if tx.paid_at else None,
                "interval": tx.interval,
                "payment_status": payment_status
            }
            
            return api_response(
                success=True,
                message="Payment verified successfully",
                data=response_data,
                status_code=200
            )
        elif payment_status == "failed":
            tx.status = "failed"
            db.session.commit()
            
            return api_response(
                success=False,
                message="Payment failed",
                data={"reference": tx.reference, "status": "failed"},
                status_code=200
            )
        else:
            # Pending or other status
            return api_response(
                success=True,
                message=f"Payment status: {payment_status}",
                data={"reference": tx.reference, "status": tx.status, "payment_status": payment_status},
                status_code=200
            )
        
    except Exception as e:
        logger.error(f"Error verifying transaction {reference}: {e}")
        return api_response(
            success=False,
            message="Verification failed",
            errors={"verification": str(e)},
            status_code=500
        )

@payment_bp.post("/webhook/paystack")
def paystack_webhook():
    """
    Handle Paystack webhook for asynchronous payment notifications.
    """
    signature = request.headers.get("x-paystack-signature")
    payload = request.data
    
    # Verify webhook signature
    computed = hmac.new(
        current_app.config["PAYSTACK_SECRET_KEY"].encode(),
        payload,
        hashlib.sha512
    ).hexdigest()
    
    if computed != signature:
        logger.warning("Invalid webhook signature")
        abort(401)
    
    event = request.json
    logger.info(f"Received webhook event: {event.get('event')}")
    
    if event["event"] == "charge.success":
        reference = event["data"]["reference"]
        
        # Find and update transaction
        tx = Transaction.query.filter_by(reference=reference).first()
        
        if tx and tx.status != "completed":
            tx.status = "completed"
            tx.paid_at = datetime.utcnow()
            tx.paystack_data = event
            db.session.commit()
            logger.info(f"Transaction {reference} marked as completed via webhook")
    
    return "ok", 200

