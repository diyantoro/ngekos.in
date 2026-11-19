import math
from typing import List, Dict, Any, Tuple

POPULAR_FACILITIES = [
    "wifi",
    "ac",
    "kamar mandi dalam",
    "water heater",
    "kasur",
    "lemari",
    "meja belajar",
    "dapur bersama",
    "parkir motor",
    "parkir mobil",
    "mesin cuci",
    "kulkas",
    "tv",
    "listrik termasuk",
]

POPULAR_AREAS = [
    "coblong",
    "dago",
    "sukajadi",
    "cibeunying",
    "lengkong",
    "buahbatu",
    "depok",
    "yogyakarta",
    "surabaya",
    "malang",
    "lainnya",
]

POPULAR_TYPES = ["putra", "putri", "campur"]

def normalize_text(text: str) -> str:
    return text.strip().lower()

def extract_features(
    luas_m2: float,
    jarak_km: float,
    kawasan: str,
    tipe_penghuni: str,
    fasilitas: List[str]
) -> Tuple[List[float], List[str]]:
    """
    Ekstraksi vektor fitur terstandar.
    Mengembalikan (vektor_fitur, daftar_nama_fitur).
    """
    feature_names = ["luas_m2", "jarak_km"]
    features = [float(luas_m2), float(jarak_km)]

    # 1. Tipe Penghuni One-Hot
    norm_tipe = normalize_text(tipe_penghuni or "campur")
    for t in POPULAR_TYPES:
        feature_names.append(f"tipe_{t}")
        features.append(1.0 if norm_tipe == t else 0.0)

    # 2. Kawasan One-Hot
    norm_kawasan = normalize_text(kawasan or "lainnya")
    matched_area = False
    for area in POPULAR_AREAS[:-1]:
        feature_names.append(f"area_{area}")
        if area in norm_kawasan:
            features.append(1.0)
            matched_area = True
        else:
            features.append(0.0)
    
    feature_names.append("area_lainnya")
    features.append(1.0 if not matched_area else 0.0)

    # 3. Fasilitas Multi-Hot
    fasilitas_set = {normalize_text(f) for f in fasilitas}
    for fac in POPULAR_FACILITIES:
        feature_names.append(f"fac_{fac.replace(' ', '_')}")
        has_fac = 1.0 if any(fac in f for f in fasilitas_set) else 0.0
        features.append(has_fac)

    # 4. Total Fasilitas (Skor kelengkapan agregat)
    feature_names.append("total_fasilitas")
    features.append(float(len(fasilitas_set)))

    return features, feature_names
