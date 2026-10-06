import cv2
import numpy as np
import os
import glob

# 1. DIREKTORI (Silakan sesuaikan jika folder output ingin diubah)
source_dir = r"C:\Users\MCC2104001_003\Pictures\shaft pto\closeup\bw"
dest_dir = r"C:\Users\MCC2104001_003\Pictures\shaft pto\closeup\bw_enhanced_v4"

if not os.path.exists(dest_dir):
    os.makedirs(dest_dir)

image_paths = glob.glob(os.path.join(source_dir, '**', '*.jpg'), recursive=True)

count = 0
for img_path in image_paths:
    img = cv2.imread(img_path)
    if img is None:
        continue
        
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    # ---------------------------------------------------------
    # AREA PARAMETER YANG BISA ANDA UBAH (TWEAKING)
    # ---------------------------------------------------------
    
    # 2. CLAHE (Kontras)
    # clipLimit: Semakin besar angkanya (misal 5.0 atau 10.0), kontras gambar akan semakin tajam dan "kasar". 
    # tileGridSize: Ukuran area lokal untuk perataan cahaya. Biarkan (8,8) atau coba (4,4) / (16,16).
    clahe = cv2.createCLAHE(clipLimit=7.0, tileGridSize=(4,4))
    contrast = clahe.apply(gray)
    
    # 3. GAUSSIAN BLUR (Penghilang Noise)
    # Kuncinya ada di (5, 5). Harus angka ganjil (misal (3,3), (5,5), (7,7)).
    # Semakin besar angkanya, gambar semakin blur sebelum dideteksi tepi (membantu agar noise debu/goresan hilang).
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    
    # 4. CANNY EDGE DETECTION (Pendeteksi Tepi)
    # Dua angka di sini (20 dan 80) adalah Treshold bawah dan atas.
    # Jika tepi kurang banyak/masih samar, KECILKAN angkanya (misal 10, 50).
    # Jika terlalu banyak garis "sampah" yang tidak perlu, BESARKAN angkanya (misal 50, 150 atau 100, 200).
    edges = cv2.Canny(blurred, 50, 150)
    
    # 5. DILATION (Ketebalan Garis Hitam)
    # (3,3) adalah ukuran penebal. Bisa diubah jadi (5,5) untuk garis yang SANGAT tebal.
    # iterations=1 adalah berapa kali penebalan diulang. Coba ubah jadi iterations=2 untuk lebih tebal.
    kernel = np.ones((1,1), np.uint8)
    thick_edges = cv2.dilate(edges, kernel, iterations=1)
    
    # ---------------------------------------------------------
    
    # Invert agar garis jadi hitam (0) dan background putih (255)
    edges_inv = cv2.bitwise_not(thick_edges)
    
    # Tempelkan garis hitam ke gambar yang sudah dikontras
    final_result = cv2.bitwise_and(contrast, edges_inv)
    
    # Simpan
    rel_path = os.path.relpath(img_path, source_dir)
    dest_path = os.path.join(dest_dir, rel_path)
    os.makedirs(os.path.dirname(dest_path), exist_ok=True)
    
    cv2.imwrite(dest_path, final_result)
    count += 1
    if count % 50 == 0:
        print(f"Processed {count} images...")

print(f"Done! Processed {count} images total. Saved to: {dest_dir}")
