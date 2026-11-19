import time
from typing import Optional
from fastapi import FastAPI, Header, HTTPException, Depends, status
from fastapi.middleware.cors import CORSMiddleware
from app.config import settings
from app.schemas import (
    PredictRequest,
    PredictResponse,
    BatchPredictRequest,
    BatchPredictResponse,
    ModelInfoResponse,
    TrainRequest,
    TrainResponse,
)
from app.model import pricing_model
from app.preprocessor import POPULAR_FACILITIES, POPULAR_AREAS

app = FastAPI(
    title=settings.SERVICE_NAME,
    version=settings.SERVICE_VERSION,
    description="Microservice Prediksi Harga Wajar Kamar Kos (Jaringan Syaraf Tiruan MLP Regresi) - Ngekos.in",
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=False,
    allow_methods=["*"],
    allow_headers=["*"],
)

def verify_token(authorization: Optional[str] = Header(None)) -> bool:
    if not settings.API_TOKEN:
        return True
    if not authorization:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Header Authorization Bearer token diperlukan."
        )
    token = authorization.replace("Bearer ", "").strip()
    if token != settings.API_TOKEN:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Token autentikasi tidak valid."
        )
    return True

@app.get("/health", tags=["Monitoring"])
def health_check():
    return {
        "status": "healthy",
        "service": settings.SERVICE_NAME,
        "active_model": pricing_model.version,
        "timestamp": int(time.time()),
    }

@app.post("/predict", response_model=PredictResponse, tags=["Inference"])
def predict_single(req: PredictRequest, _auth: bool = Depends(verify_token)):
    try:
        result = pricing_model.predict(
            luas_m2=req.luas_m2,
            jarak_km=req.jarak_km,
            kawasan=req.kawasan or "Coblong",
            tipe_penghuni=req.tipe_penghuni or "Campur",
            fasilitas=req.fasilitas or [],
            harga_aktual=req.harga_aktual,
        )
        result["kamar_id"] = req.kamar_id
        return result
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Gagal memprediksi harga: {str(e)}")

@app.post("/predict/batch", response_model=BatchPredictResponse, tags=["Inference"])
def predict_batch(req: BatchPredictRequest, _auth: bool = Depends(verify_token)):
    try:
        predictions = []
        for item in req.items:
            res = pricing_model.predict(
                luas_m2=item.luas_m2,
                jarak_km=item.jarak_km,
                kawasan=item.kawasan or "Coblong",
                tipe_penghuni=item.tipe_penghuni or "Campur",
                fasilitas=item.fasilitas or [],
                harga_aktual=item.harga_aktual,
            )
            res["kamar_id"] = item.kamar_id
            predictions.append(res)

        return {
            "model_versi": pricing_model.version,
            "total": len(predictions),
            "predictions": predictions,
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Gagal memprediksi batch: {str(e)}")

@app.get("/model/info", response_model=ModelInfoResponse, tags=["Model Management"])
def model_info(_auth: bool = Depends(verify_token)):
    return {
        "versi": pricing_model.version,
        "algoritma": "Multilayer Perceptron (MLP Regresi)",
        "status": "aktif",
        "mae": pricing_model.metrics.get("mae", 0.0),
        "rmse": pricing_model.metrics.get("rmse", 0.0),
        "r2": pricing_model.metrics.get("r2", 0.0),
        "mape": pricing_model.metrics.get("mape", 0.0),
        "hyperparameter": pricing_model.hyperparameters,
        "features": ["luas_m2", "jarak_km", "tipe_penghuni", "kawasan", *POPULAR_FACILITIES],
    }

@app.post("/train", response_model=TrainResponse, tags=["Training"])
def train_model(req: TrainRequest, _auth: bool = Depends(verify_token)):
    try:
        metrics = pricing_model.train(req.data)
        return {
            "versi": pricing_model.version,
            "pesan": "Pelatihan model selesai dan artefak berhasil disimpan.",
            "metrik": metrics,
        }
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Pelatihan model gagal: {str(e)}")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("app.main:app", host="0.0.0.0", port=8000, reload=True)
