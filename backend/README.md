# RiceGuard Detection Backend

Backend ini untuk deploy di Railway. Copy `best.pt` ke folder `backend/` sebelum deploy, atau set environment variable `MODEL_PATH` ke lokasi model.

## Railway

Start command:

```bash
uvicorn main:app --host 0.0.0.0 --port $PORT
```

Environment optional:

- `MODEL_PATH`: path model YOLO, default `backend/best.pt`
- `CORS_ORIGINS`: domain frontend, contoh `https://riceguard.vercel.app`

Endpoint:

- `GET /`
- `POST /detect` dengan form-data field `image`
