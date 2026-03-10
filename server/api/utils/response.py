# api/utils/response.py
from flask import jsonify, request
from datetime import datetime
from typing import Any, Optional, Union, List
from api.models.transaction import Transaction  # Adjust import as needed

def api_response(
    success: bool,
    message: str,
    data: Any = None,
    errors: Optional[Union[dict, list, str]] = None,
    status_code: int = None,
    meta: Optional[dict] = None
):
    """
    Centralized API response helper.
    Ensures consistent JSON response format across all endpoints.
    """
    if status_code is None:
        status_code = 200 if success else 400

    # Handle SQLAlchemy models
    if hasattr(data, '__dict__') and not isinstance(data, dict):
        if hasattr(data, 'to_dict'):
            data = data.to_dict()
        elif hasattr(data, '_asdict'):
            data = data._asdict()
        else:
            # Remove SQLAlchemy internal attributes
            data = {k: v for k, v in data.__dict__.items() 
                   if not k.startswith('_') and not callable(v)}
    
    # Handle lists of models
    elif isinstance(data, list) and data:
        if hasattr(data[0], 'to_dict'):
            data = [item.to_dict() for item in data]
        elif hasattr(data[0], '__dict__'):
            data = [{k: v for k, v in item.__dict__.items() 
                    if not k.startswith('_') and not callable(v)} 
                   for item in data]

    payload = {
        "success": success,
        "message": message,
        "timestamp": datetime.utcnow().isoformat() + "Z",
    }

    if request:
        payload["path"] = request.path
    
    if data is not None:
        payload["data"] = data
    
    if errors is not None:
        payload["errors"] = errors
    
    if meta is not None:
        payload["meta"] = meta

    response = jsonify(payload)
    response.status_code = status_code
    return response

