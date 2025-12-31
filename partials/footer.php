    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- About Section -->
                <div class="lg:col-span-2">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center mr-3">
                            <i class="fas fa-mosque text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold"><?php echo htmlspecialchars($settings['site_name']); ?></h3>
                            <p class="text-sm text-gray-300">Masjid & Bimbel</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-4 leading-relaxed">
                        <?php echo htmlspecialchars($settings['site_description']); ?>
                        Kami berkomitmen untuk menyediakan tempat ibadah yang nyaman dan pendidikan berkualitas 
                        melalui program bimbingan belajar untuk jenjang SD, SMP, dan SMA.
                    </p>
                    <div class="flex space-x-4">
                        <?php 
                        $social_links = getSocialMediaLinks();
                        if (!empty($social_links['facebook'])): 
                        ?>
                        <a href="<?php echo htmlspecialchars($social_links['facebook']); ?>" target="_blank" class="text-gray-300 hover:text-green-400 transition duration-200" aria-label="Facebook">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_links['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($social_links['instagram']); ?>" target="_blank" class="text-gray-300 hover:text-green-400 transition duration-200" aria-label="Instagram">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_links['youtube'])): ?>
                        <a href="<?php echo htmlspecialchars($social_links['youtube']); ?>" target="_blank" class="text-gray-300 hover:text-green-400 transition duration-200" aria-label="YouTube">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_links['twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($social_links['twitter']); ?>" target="_blank" class="text-gray-300 hover:text-green-400 transition duration-200" aria-label="Twitter">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_links['telegram'])): ?>
                        <a href="<?php echo htmlspecialchars($social_links['telegram']); ?>" target="_blank" class="text-gray-300 hover:text-green-400 transition duration-200" aria-label="Telegram">
                            <i class="fab fa-telegram text-xl"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php 
                        $whatsapp_link = getWhatsAppLink();
                        if ($whatsapp_link !== '#'): 
                        ?>
                        <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="text-gray-300 hover:text-green-400 transition duration-200" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Menu Utama</h3>
                    <div class="space-y-2">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-home mr-2 text-sm"></i>Beranda
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-info-circle mr-2 text-sm"></i>Profil Masjid
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_sholat.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-clock mr-2 text-sm"></i>Jadwal Sholat
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_jumat.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-calendar-alt mr-2 text-sm"></i>Jadwal Jumat
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-newspaper mr-2 text-sm"></i>Berita & Kegiatan
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/galeri.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-images mr-2 text-sm"></i>Galeri
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-hand-holding-heart mr-2 text-sm"></i>Donasi
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/kontak.php" 
                           class="block text-gray-300 hover:text-green-400 transition duration-200">
                            <i class="fas fa-envelope mr-2 text-sm"></i>Kontak
                        </a>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kontak Kami</h3>
                    <div class="space-y-3">
                        <?php 
                        $contact_info = getContactInfo();
                        if (!empty($contact_info['address'])): 
                        ?>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt mr-3 mt-1 text-green-400"></i>
                            <div>
                                <p class="text-gray-300 text-sm leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($contact_info['address'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($contact_info['phone'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-3 text-green-400"></i>
                            <a href="tel:<?php echo htmlspecialchars($contact_info['phone']); ?>" 
                               class="text-gray-300 hover:text-green-400 transition duration-200">
                                <?php echo htmlspecialchars($contact_info['phone']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($contact_info['email'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-green-400"></i>
                            <a href="mailto:<?php echo htmlspecialchars($contact_info['email']); ?>" 
                               class="text-gray-300 hover:text-green-400 transition duration-200">
                                <?php echo htmlspecialchars($contact_info['email']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-3 text-green-400"></i>
                            <div class="text-gray-300 text-sm">
                                <p>Buka 24 jam</p>
                                <p class="text-xs text-gray-400">Untuk ibadah dan kegiatan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Footer -->
            <div class="border-t border-gray-700 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-center md:text-left mb-4 md:mb-0">
                        <p class="text-gray-300 text-sm">
                            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name']); ?>. 
                            All rights reserved.
                        </p>
                        <p class="text-gray-400 text-xs mt-1">
                            Dikembangkan dengan ❤️ untuk kemajuan umat
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-300">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/privacy.php" 
                           class="hover:text-green-400 transition duration-200">
                            Kebijakan Privasi
                        </a>
                        <span class="text-gray-500">|</span>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/terms.php" 
                           class="hover:text-green-400 transition duration-200">
                            Syarat & Ketentuan
                        </a>
                        <span class="text-gray-500">|</span>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                           class="hover:text-green-400 transition duration-200">
                            <i class="fas fa-sign-in-alt mr-1"></i>Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" 
            class="fixed bottom-6 right-6 bg-green-600 text-white p-3 rounded-full shadow-lg hover:bg-green-700 transition duration-200 opacity-0 invisible"
            aria-label="Kembali ke atas">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                    
                    // Toggle icon
                    const icon = this.querySelector('i');
                    if (mobileMenu.classList.contains('hidden')) {
                        icon.className = 'fas fa-bars text-xl';
                    } else {
                        icon.className = 'fas fa-times text-xl';
                    }
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('hidden');
                        mobileMenuButton.querySelector('i').className = 'fas fa-bars text-xl';
                    }
                });
            }
            
            // Back to top button
            const backToTopButton = document.getElementById('backToTop');
            
            if (backToTopButton) {
                // Show/hide back to top button
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopButton.classList.remove('opacity-0', 'invisible');
                        backToTopButton.classList.add('opacity-100', 'visible');
                    } else {
                        backToTopButton.classList.add('opacity-0', 'invisible');
                        backToTopButton.classList.remove('opacity-100', 'visible');
                    }
                });
                
                // Smooth scroll to top
                backToTopButton.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
            
            // Lazy loading for images
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        });
        
        // Prayer time update (placeholder for API integration)
        function updatePrayerTimes() {
            // This would integrate with actual prayer time API
            console.log('Prayer times would be updated here');
        }
        
        // Update prayer times every hour
        setInterval(updatePrayerTimes, 3600000);
        
        // Performance optimization: Preload critical resources
        function preloadCriticalResources() {
            const criticalImages = [
                "<?= $base_url ?>/assets/images/masjid-hero.jpg",
                "<?= $base_url ?>/assets/images/favicon.svg"
            ];
            
            criticalImages.forEach(src => {
                const link = document.createElement('link');
                link.rel = 'preload';
                link.as = 'image';
                link.href = src;
                document.head.appendChild(link);
            });
        }
        
        // Call preload function
        preloadCriticalResources();
    </script>
    
    <!-- Additional page-specific scripts -->
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>