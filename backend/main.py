import os
import uuid
from pathlib import Path

import cv2
from fastapi import FastAPI, File, HTTPException, Request, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from ultralytics import YOLO


BASE_DIR = Path(__file__).resolve().parent
MODEL_PATH = Path(os.getenv("MODEL_PATH", BASE_DIR / "best.pt"))
UPLOAD_DIR = Path(os.getenv("UPLOAD_DIR", BASE_DIR / "uploads"))
RESULT_DIR = Path(os.getenv("RESULT_DIR", BASE_DIR / "results"))

UPLOAD_DIR.mkdir(parents=True, exist_ok=True)
RESULT_DIR.mkdir(parents=True, exist_ok=True)

app = FastAPI(title="RiceGuard Detection API")
app.add_middleware(
    CORSMiddleware,
    allow_origins=os.getenv("CORS_ORIGINS", "*").split(","),
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
app.mount("/results", StaticFiles(directory=RESULT_DIR), name="results")

model = YOLO(str(MODEL_PATH))


@app.get("/")
def health_check():
    return {"status": "ok", "service": "riceguard-detection-api"}


@app.post("/detect")
async def detect(request: Request, image: UploadFile = File(...)):
    if not image.content_type or not image.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="File harus berupa gambar.")

    suffix = Path(image.filename or "").suffix.lower() or ".jpg"
    image_name = f"{uuid.uuid4().hex}{suffix}"
    result_name = f"{uuid.uuid4().hex}.jpg"
    image_path = UPLOAD_DIR / image_name
    result_path = RESULT_DIR / result_name

    try:
        image_path.write_bytes(await image.read())
        results = model(str(image_path), verbose=False)
        plotted = results[0].plot()

        if not cv2.imwrite(str(result_path), plotted):
            raise RuntimeError("Gagal menyimpan gambar hasil deteksi.")

        result_url = str(request.base_url).rstrip("/") + f"/results/{result_name}"
        detections = []

        for box in results[0].boxes:
            cls_id = int(box.cls[0])
            conf = float(box.conf[0])
            class_name = results[0].names[cls_id]
            detections.append(
                {
                    "class": class_name,
                    "confidence": round(conf, 2),
                    "output": result_url,
                }
            )

        if not detections:
            detections.append(
                {
                    "class": "Tidak terdeteksi",
                    "confidence": 0,
                    "output": result_url,
                }
            )

        return {"detections": detections, "result_image": result_url}
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc
    finally:
        image_path.unlink(missing_ok=True)
