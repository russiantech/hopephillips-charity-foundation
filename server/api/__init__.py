from flask import Flask
from flask_cors import CORS

from api.config import Config
from api.extensions import db, migrate

from api.routes.payments import payment_bp
from api.webhooks.paystack import webhook_bp


def create_app():

    app = Flask(__name__)

    app.config.from_object(Config)

    db.init_app(app)
    migrate.init_app(app, db)

    CORS(app)

    app.register_blueprint(payment_bp)
    app.register_blueprint(webhook_bp)

    return app

