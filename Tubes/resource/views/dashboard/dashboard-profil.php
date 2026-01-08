<?php
// resource/views/pages/dashboard-profil.php
// Data $d_user dan $foto_profil sudah tersedia dari include di dashboard.php
?>
<div class="animate-in">
    <div style="background: white; border-radius: 24px; padding: 2.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(6,78,59,0.2);">
                <i class="bi bi-person-gear" style="font-size: 1.5rem; color: white;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--pustani-green);">Pengaturan Profil</h3>
                <p class="text-muted small mb-0">Kelola informasi profil dan kredensial Anda</p>
            </div>
        </div>

        <div style="height: 2px; background: linear-gradient(90deg, var(--pustani-green), transparent); margin-bottom: 2rem; border-radius: 2px;"></div>

        <form action="" method="POST" enctype="multipart/form-data">

            <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 20px; padding: 2rem; text-align: center; margin-bottom: 2rem;">
                <div style="margin-bottom: 1.5rem;">
                    <img src="<?= $foto_profil ?>"
                        alt="Profile"
                        id="display_avatar"
                        style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                </div>

                <div style="max-width: 400px; margin: 0 auto;">
                    <label class="form-label fw-bold mb-2" style="color: #475569;">Foto Profil</label>
                    <input type="file"
                        name="foto"
                        class="form-control"
                        accept="image/*"
                        style="border-radius: 14px; padding: 0.75rem 1rem; border: 2px solid #e2e8f0;"
                        onchange="previewProfilePic(this)">
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Format: JPG, JPEG, PNG. Maksimal 2MB
                    </small>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Username</label>
                    <div style="position: relative;">
                        <i class="bi bi-person" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text"
                            name="username"
                            class="form-control"
                            value="<?= htmlspecialchars($d_user['username']) ?>"
                            required
                            style="border-radius: 14px; padding: 0.95rem 1rem 0.95rem 3rem; border: 2px solid #e2e8f0; font-size: 0.95rem; transition: all 0.3s ease;"
                            onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">No. Handphone</label>
                    <div style="position: relative;">
                        <i class="bi bi-telephone" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text"
                            name="no_hp"
                            class="form-control"
                            value="<?= htmlspecialchars($d_user['no_hp'] ?? '') ?>"
                            placeholder="08xxxxxxxxxx"
                            style="border-radius: 14px; padding: 0.95rem 1rem 0.95rem 3rem; border: 2px solid #e2e8f0; font-size: 0.95rem; transition: all 0.3s ease;"
                            onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Alamat Lengkap</label>
                    <div style="position: relative;">
                        <i class="bi bi-geo-alt" style="position: absolute; left: 1rem; top: 1rem; color: #94a3b8;"></i>
                        <textarea name="alamat"
                            class="form-control"
                            style="border-radius: 14px; padding: 0.95rem 1rem 0.95rem 3rem; border: 2px solid #e2e8f0; font-size: 0.95rem; transition: all 0.3s ease;"
                            rows="1"
                            onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"><?= htmlspecialchars($d_user['alamat'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Bidang Keahlian</label>
                    <div style="position: relative;">
                        <i class="bi bi-mortarboard" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text"
                            name="keahlian"
                            class="form-control"
                            value="<?= htmlspecialchars($d_user['keahlian'] ?? '') ?>"
                            placeholder="Contoh: Pertanian Organik"
                            style="border-radius: 14px; padding: 0.95rem 1rem 0.95rem 3rem; border: 2px solid #e2e8f0; font-size: 0.95rem; transition: all 0.3s ease;"
                            onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label" style="color: #334155; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Biografi</label>
                    <textarea name="bio"
                        class="form-control"
                        rows="4"
                        placeholder="Ceritakan tentang latar belakang dan pengalaman Anda di bidang pertanian..."
                        style="border-radius: 14px; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; font-size: 0.95rem; line-height: 1.8; transition: all 0.3s ease;"
                        onfocus="this.style.borderColor='var(--pustani-green)'; this.style.boxShadow='0 0 0 4px rgba(6,78,59,0.1)';"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"><?= htmlspecialchars($d_user['bio'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end pt-3">
                <a href="dashboard.php"
                    class="btn"
                    style="background: #f1f5f9; color: #475569; border: none; border-radius: 14px; padding: 0.85rem 2rem; font-weight: 700; transition: all 0.3s ease;">
                    Batal
                </a>
                <button type="submit"
                    name="simpan_profil_dashboard"
                    class="btn"
                    style="background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%); color: white; border: none; border-radius: 14px; padding: 0.85rem 2.5rem; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 14px rgba(6,78,59,0.25);"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(6,78,59,0.3)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(6,78,59,0.25)';">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fungsi Preview Foto Profil secara realtime
    function previewProfilePic(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('display_avatar').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>