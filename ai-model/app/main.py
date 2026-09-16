from flask import Flask, request, jsonify
from flask_cors import CORS
from collections import defaultdict
import logging
import sys
import os
import time

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from .config import Config
from .services.ai_service import AIService

app = Flask(__name__)
CORS(app, origins='*')

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

if not Config.validate():
    logger.warning("GigaChat credentials not set. AI will use fallback mode.")

ai_service = AIService()

_request_log: dict = defaultdict(list)
RATE_LIMIT_WINDOW = 3600
RATE_LIMIT_MAX = 1        

def _is_rate_limited(key: str) -> bool:
    now = time.time()
    _request_log[key] = [t for t in _request_log[key] if now - t < RATE_LIMIT_WINDOW]
    if len(_request_log[key]) >= RATE_LIMIT_MAX:
        return True
    _request_log[key].append(now)
    return False

@app.route('/analyze', methods=['POST'])
def analyze():
    try:
        data = request.get_json()

        if not data:
            return jsonify({'success': False, 'error': 'No data'}), 400

        key = (data.get('email') or request.remote_addr or 'unknown').lower()
        if _is_rate_limited(key):
            logger.warning(f"Rate limit exceeded for {key}")
            return jsonify({
                'success': False,
                'error': 'Rate limit exceeded. Try again later.'
            }), 429

        required = ['name', 'email', 'comment']
        missing = [f for f in required if f not in data]
        if missing:
            return jsonify({
                'success': False,
                'error': f'Missing fields: {", ".join(missing)}'
            }), 400

        result = ai_service.analyze(
            data['name'],
            data['email'],
            data['comment']
        )

        logger.info(f"Analysis: {result['category']}, AI: {result['ai_used']}, Fallback: {result['fallback']}")

        return jsonify({'success': True, 'data': result})

    except Exception as e:
        logger.error(f"Error: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'ok',
        'service': 'AI',
        'ai_available': ai_service.ai_available
    })

@app.route('/ping', methods=['GET'])
def ping():
    return jsonify({'pong': True})

if __name__ == '__main__':
    port = Config.FLASK_PORT
    print(f"Starting Flask AI on port {port}")
    print(f"AI available: {ai_service.ai_available}")
    app.run(host='0.0.0.0', port=port, debug=Config.FLASK_DEBUG)