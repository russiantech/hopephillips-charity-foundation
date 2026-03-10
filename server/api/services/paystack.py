# import requests
# from flask import current_app


# class PaystackService:

#     BASE_URL = "https://api.paystack.co"


#     @staticmethod
#     def headers():

#         return {
#             "Authorization": f"Bearer {current_app.config['PAYSTACK_SECRET_KEY']}",
#             "Content-Type": "application/json"
#         }


#     @staticmethod
#     def create_plan(amount_ngn, interval):

#         url = f"{PaystackService.BASE_URL}/plan"

#         payload = {
#             "name": f"{interval}_{amount_ngn}",
#             "interval": interval,
#             "amount": amount_ngn * 100,
#             "currency": "NGN"
#         }

#         res = requests.post(url, json=payload, headers=PaystackService.headers())

#         return res.json()


#     @staticmethod
#     def initialize_transaction(email, amount_ngn, reference, plan_code=None):

#         url = f"{PaystackService.BASE_URL}/transaction/initialize"

#         # Get organization details from config
#         org_name = current_app.config.get('APP_NAME', 'Hope Phillips Charity Foundation')
#         org_logo = current_app.config.get('APP_LOGO_URL', '')
#         org_description = current_app.config.get('ORG_DESCRIPTION', 'Support The Vulnerable')

#         payload = {
#             "email": email,
#             "amount": amount_ngn * 100,
#             "reference": reference,
#             "currency": "NGN"
#         }

#          # Customization options
#         customizations = {
#             "title": org_name,
#             "description": metadata.get('description', org_description) if metadata else org_description,
#             "logo": org_logo
#         }

#         if plan_code:
#             payload["plan"] = plan_code

#         res = requests.post(url, json=payload, headers=PaystackService.headers())
#         print('RES:', res)
#         return res.json()


#     @staticmethod
#     def verify_transaction(reference):

#         url = f"{PaystackService.BASE_URL}/transaction/verify/{reference}"

#         res = requests.get(url, headers=PaystackService.headers())

#         return res.json()



# # v2
import requests
from flask import current_app


class PaystackService:

    BASE_URL = "https://api.paystack.co"

    # -----------------------------------
    # Headers
    # -----------------------------------
    @staticmethod
    def headers():
        return {
            "Authorization": f"Bearer {current_app.config['PAYSTACK_SECRET_KEY']}",
            "Content-Type": "application/json"
        }

    # -----------------------------------
    # Utility: truncate description
    # -----------------------------------
    @staticmethod
    def truncate_description(text: str, limit: int = 120) -> str:
        """
        Ensures Paystack description stays short and clean
        """
        if not text:
            return ""

        text = text.strip()

        if len(text) <= limit:
            return text

        return text[:limit].rstrip() + "..."

    # -----------------------------------
    # Create Plan (for subscriptions)
    # -----------------------------------
    @staticmethod
    def create_plan(amount_ngn, interval):

        url = f"{PaystackService.BASE_URL}/plan"

        payload = {
            "name": f"{interval}_{amount_ngn}",
            "interval": interval,
            "amount": amount_ngn * 100,
            "currency": "NGN"
        }

        res = requests.post(
            url,
            json=payload,
            headers=PaystackService.headers(),
            timeout=15
        )

        return res.json()

    # -----------------------------------
    # Initialize Transaction
    # -----------------------------------
    @staticmethod
    def initialize_transaction(
        email,
        amount_ngn,
        reference,
        description=None,
        metadata=None,
        plan_code=None
    ):

        url = f"{PaystackService.BASE_URL}/transaction/initialize"

        # Config values
        org_name = current_app.config.get(
            "APP_NAME",
            "Hope Phillips Charity Foundation"
        )

        org_logo = current_app.config.get(
            "APP_LOGO_URL",
            ""
        )

        org_description = current_app.config.get(
            "App_DESCRIPTION",
            "Support The Vulnerable"
        )

        # Determine description priority
        description_text = description or org_description

        # truncate for clean Paystack UI
        description_text = PaystackService.truncate_description(description_text)

        payload = {
            "email": email,
            "amount": amount_ngn * 100,
            "reference": reference,
            "currency": "NGN",

            # Optional callback
            "callback_url": current_app.config.get("PAYSTACK_CALLBACK_URL"),

            # Metadata for analytics / backend usage
            "metadata": metadata or {},

            # Paystack checkout customization
            "customizations": {
                "title": org_name,
                "description": description_text,
                "logo": org_logo
            }
        }

        if plan_code:
            payload["plan"] = plan_code

        res = requests.post(
            url,
            json=payload,
            headers=PaystackService.headers(),
            timeout=15
        )

        return res.json()

    # -----------------------------------
    # Verify Transaction
    # -----------------------------------
    @staticmethod
    def verify_transaction(reference):

        url = f"{PaystackService.BASE_URL}/transaction/verify/{reference}"

        res = requests.get(
            url,
            headers=PaystackService.headers(),
            timeout=15
        )

        return res.json()

