import sys
import json
import os
from ultralytics import YOLO
import cv2
import time

import logging
logging.getLogger("ultralytics").setLevel(logging.ERROR)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
model = YOLO(os.path.join(BASE_DIR, "best.pt"))

image_path = sys.argv[1]

results = model(image_path, verbose=False)

# pastikan folder ada
os.makedirs(os.path.join(BASE_DIR, "results"), exist_ok=True)

# 🔥 nama unik
filename = f"{int(time.time())}.jpg"
output_path = os.path.join(BASE_DIR, "results", filename)

# simpan hasil
result_img = results[0].plot()
cv2.imwrite(output_path, result_img)

# path relatif ke web
relative_path = f"results/{filename}"

detections = []

for box in results[0].boxes:
    cls_id = int(box.cls[0])
    conf = float(box.conf[0])
    class_name = results[0].names[cls_id]

    detections.append({
        "class": class_name,
        "confidence": round(conf, 2),
        "output": relative_path   # 🔥 kirim ke PHP
    })

if len(detections) == 0:
    detections.append({
        "class": "Tidak terdeteksi",
        "confidence": 0,
        "output": relative_path
    })

print(json.dumps(detections))

# import sys
# import json
# import os
# from ultralytics import YOLO
# import cv2

# # Matikan log YOLO
# import logging
# logging.getLogger("ultralytics").setLevel(logging.ERROR)

# BASE_DIR = os.path.dirname(os.path.abspath(__file__))
# model = YOLO(os.path.join(BASE_DIR, "best.pt"))

# image_path = sys.argv[1]

# results = model(image_path, verbose=False)

# os.makedirs("results", exist_ok=True)

# output_path = os.path.join(BASE_DIR, "results", "output.jpg")
# result_img = results[0].plot()
# cv2.imwrite(output_path, result_img)

# detections = []

# for box in results[0].boxes:
#     cls_id = int(box.cls[0])
#     conf = float(box.conf[0])
#     class_name = results[0].names[cls_id]

#     detections.append({
#         "class": class_name,
#         "confidence": round(conf, 2)
#     })

# if len(detections) == 0:
#     detections.append({
#         "class": "Tidak terdeteksi",
#         "confidence": 0
#     })

# print(json.dumps(detections))