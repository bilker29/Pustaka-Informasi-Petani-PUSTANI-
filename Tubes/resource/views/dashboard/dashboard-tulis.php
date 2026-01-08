<?php
// resource/views/pages/dashboard-tulis.php
?>
<div class="animate-in">
    <div style="background: white; border-radius: 24px; padding: 2.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(6,78,59,0.2);">
                <i class="bi bi-pencil-square" style="font-size: 1.5rem; color: white;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--pustani-green);">Tulis Artikel Baru</h3>
                <p class="text-muted small mb-0">Bagikan pengetahuan dan pengalaman Anda dalam bidang pertanian</p>
            </div>
        </div>

        <div style="height: 2px; background: linear-gradient(90deg, var(--pustani-green), transparent); margin-bottom: 2rem; border-radius: 2px;"></div>

        <form action="" method="POST" enctype="multipart/form-data">

            <div class="mb-4">
                <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Judul Artikel</label>
                <input type="text"
                    name="title"
                    class="form-control"
                    placeholder="Masukkan judul artikel yang menarik..."
                    required
                    style="border-radius: 14px; padding: 0.95rem 1.25rem; border: 2px solid #e2e8f0; font-size: 0.95rem; transition: all 0.3s ease;"
                    onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
            </div>

            <div class="mb-4">
                <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Kategori</label>
                <select name="category"
                    class="form-select"
                    required
                    style="border-radius: 14px; padding: 0.95rem 1.25rem; border: 2px solid #e2e8f0; font-size: 0.95rem; transition: all 0.3s ease;"
                    onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    <option value="" selected disabled>Pilih Kategori Artikel</option>
                    <option value="Tips & Trik">Tips & Trik</option>
                    <option value="Hama & Penyakit">Hama & Penyakit</option>
                    <option value="Bisnis Tani">Bisnis Tani</option>
                    <option value="Teknologi">Teknologi</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Konten Artikel</label>
                <textarea name="content"
                    class="form-control"
                    rows="12"
                    placeholder="Tulis konten artikel Anda di sini..."
                    required
                    style="border-radius: 14px; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; font-size: 0.95rem; line-height: 1.8; transition: all 0.3s ease;"
                    onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"></textarea>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Minimal 100 karakter. Gunakan paragraf yang jelas dan mudah dipahami.
                </small>
            </div>

            <div class="mb-4">
                <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Gambar Sampul</label>
                <div style="border: 2px dashed #e2e8f0; border-radius: 14px; padding: 2rem; text-align: center; transition: all 0.3s ease; background: #f8fafc;"
                    onmouseover="this.style.borderColor='var(--pustani-green)'; this.style.background='#f0fdf4';"
                    onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc';">
                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                    <input type="file"
                        name="cover_image"
                        class="form-control"
                        accept="image/*"
                        required
                        style="border: none; background: transparent; text-align: center;"
                        onchange="previewImage(this)">
                    <p class="text-muted small mb-0 mt-2">
                        Format: JPG, PNG, JPEG. Maksimal 2MB
                    </p>
                </div>
                <div id="imagePreview" style="margin-top: 1rem; display: none;">
                    <img id="preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; border-radius: 14px; padding: 1.25rem; margin-bottom: 2rem;">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-info-circle-fill" style="font-size: 1.5rem; color: var(--pustani-green); flex-shrink: 0;"></i>
                    <div>
                        <h6 class="fw-bold mb-2" style="color: var(--pustani-green);">Catatan Verifikasi</h6>
                        <ul class="mb-0" style="font-size: 0.9rem; color: #065f46; padding-left: 1.25rem;">
                            <li>Artikel akan disimpan sebagai <strong>Draft</strong> untuk divalidasi oleh Admin.</li>
                            <li>Pastikan gambar yang diunggah berkualitas baik dan relevan.</li>
                            <li>Anda akan mendapatkan notifikasi setelah artikel disetujui untuk terbit.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end">
                <a href="dashboard.php"
                    class="btn"
                    style="background: #f1f5f9; color: #475569; border: none; border-radius: 14px; padding: 0.85rem 2rem; font-weight: 700; transition: all 0.3s ease;">
                    <i class="bi bi-x-circle me-2"></i>
                    Batal
                </a>
                <button type="submit"
                    name="publish_dashboard"
                    class="btn"
                    style="background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%); color: white; border: none; border-radius: 14px; padding: 0.85rem 2rem; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 14px rgba(6,78,59,0.25);"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(6,78,59,0.3)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(6,78,59,0.25)';">
                    <i class="bi bi-send-fill me-2"></i>
                    Ajukan Artikel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        const previewContainer = document.getElementById('imagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            }

            reader.readAsDataURL(input.files[0]);
        } else {
            previewContainer.style.display = 'none';
        }
    }
</script>