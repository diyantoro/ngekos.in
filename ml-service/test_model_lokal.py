# Uji cepat modul ML tanpa FastAPI/sklearn (fallback deterministik).
#
# Pemakaian:
#   python ml-service/test_model_lokal.py
#
# Berguna di mesin yang belum memasang dependensi TensorFlow/sklearn,
# karena hanya menguji preprocessor + estimator pasar terkalibrasi.

import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.model import pricing_model
from app.preprocessor import extract_features


def jalankan() -> int:
    # 1. Ekstraksi fitur
    feats, names = extract_features(12, 1.0, "Coblong", "Putri", ["WiFi", "AC"])
    assert len(feats) == len(names), "Jumlah fitur dan nama fitur harus sama"
    assert feats[0] == 12.0 and feats[1] == 1.0

    # 2. Prediksi dengan harga aktual di bawah pasar
    murah = pricing_model.predict(12, 1.0, "Coblong", "Putri", ["WiFi", "AC", "Kamar Mandi Dalam"], 900_000)
    assert murah["harga_prediksi"] > 0
    assert murah["label_kewajaran"] == "di_bawah_pasar", murah

    # 3. Prediksi dengan harga aktual di atas pasar
    mahal_aktual = murah["harga_prediksi"] * 1.5
    mahal = pricing_model.predict(12, 1.0, "Coblong", "Putri", ["WiFi"], mahal_aktual)
    assert mahal["label_kewajaran"] == "di_atas_pasar", mahal

    # 4. Prediksi tanpa harga aktual
    tanpa = pricing_model.predict(9, 2.0, "Dago", "Putra", ["Kasur"])
    assert tanpa["selisih_persen"] is None
    assert tanpa["label_kewajaran"] == "wajar"

    # 5. Kamar lebih besar dan lebih dekat kampus harus lebih mahal
    dekat = pricing_model.predict(16, 0.5, "Coblong", "Campur", ["AC", "WiFi"])
    jauh = pricing_model.predict(16, 6.0, "Coblong", "Campur", ["AC", "WiFi"])
    assert dekat["harga_prediksi"] > jauh["harga_prediksi"], (dekat, jauh)

    print("Semua uji lokal model ML lulus.")
    print("Contoh hasil prediksi:", murah)
    return 0


if __name__ == "__main__":
    raise SystemExit(jalankan())
