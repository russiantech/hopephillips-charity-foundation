import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    APP_NAME = "Hope Phillips Charity Foundation"
    App_DESCRIPTION = "Donation to support vulnerable children across rural communities in Nigeria through education programs..."
    APP_LOGO_URL = "https://hopephillipscharityfoundation.com//images/HPCF.main.png"
    PAYMENT_LABEL = "Support the Vulnerable"
    ENV = os.getenv("ENV")
    SQLALCHEMY_DATABASE_URI = os.getenv("DATABASE_URL")
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    SQLALCHEMY_ENGINE_OPTIONS = {
    "pool_size": 10,
    "max_overflow": 20
    }
    API_BASE_URL = os.getenv("API_BASE_URL")
    PAYSTACK_SECRET_KEY = os.getenv("PAYSTACK_SECRET_KEY")
    PAYSTACK_PUBLIC_KEY = os.getenv("PAYSTACK_PUBLIC_KEY")
    PAYSTACK_WEBHOOK_SECRET = os.getenv("PAYSTACK_WEBHOOK_SECRET")
    PAYSTACK_CALLBACK_URL = "https://hopephillipscharityfoundation.com/"
    

    USD_TO_NGN_RATE = float(os.getenv("USD_TO_NGN_RATE", 1500))

