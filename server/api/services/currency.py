# services/currency_service.py

import requests

class CurrencyService:

    API = "https://api.exchangerate.host/latest?base=USD&symbols=NGN"


    @classmethod
    def usd_to_ngn(cls, amount):

        res = requests.get(cls.API).json()

        rate = res["rates"]["NGN"]

        return amount * rate

