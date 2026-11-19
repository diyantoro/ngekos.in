from typing import List, Optional, Dict, Any
from pydantic import BaseModel, Field

class PredictRequest(BaseModel):
    kamar_id: Optional[int] = None
    luas_m2: float = Field(..., gt=0, description="Luas kamar dalam meter persegi")
    jarak_km: float = Field(..., ge=0, description="Jarak ke titik acuan dalam kilometer")
    kawasan: Optional[str] = "Coblong"
    tipe_penghuni: Optional[str] = "Campur"
    fasilitas: List[str] = Field(default_factory=list, description="Daftar nama fasilitas kamar")
    harga_aktual: Optional[float] = Field(None, ge=0, description="Harga sewa riil per bulan (opsional untuk hitung selisih)")

class PredictResponse(BaseModel):
    kamar_id: Optional[int] = None
    harga_prediksi: int
    selisih_persen: Optional[float] = None
    label_kewajaran: str
    model_versi: str
    detail: Optional[Dict[str, Any]] = None

class BatchPredictRequest(BaseModel):
    items: List[PredictRequest]

class BatchPredictResponse(BaseModel):
    model_versi: str
    total: int
    predictions: List[PredictResponse]

class ModelInfoResponse(BaseModel):
    versi: str
    algoritma: str
    status: str
    mae: float
    rmse: float
    r2: float
    mape: float
    hyperparameter: Dict[str, Any]
    features: List[str]

class TrainRequest(BaseModel):
    data: List[Dict[str, Any]]
    epochs: Optional[int] = 100
    learning_rate: Optional[float] = 0.001

class TrainResponse(BaseModel):
    versi: str
    pesan: str
    metrik: Dict[str, float]
