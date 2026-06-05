FROM python:3.12-slim

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends libglib2.0-0 libgl1 \
    && rm -rf /var/lib/apt/lists/*

COPY backend/requirements.txt /app/backend/requirements.txt
RUN python -m pip install --upgrade pip \
    && python -m pip install --no-cache-dir -r /app/backend/requirements.txt

COPY backend /app/backend
COPY best.pt /app/best.pt

EXPOSE 8000

CMD ["sh", "-c", "cd /app/backend && MODEL_PATH=/app/best.pt python -m uvicorn main:app --host 0.0.0.0 --port ${PORT:-8000}"]
