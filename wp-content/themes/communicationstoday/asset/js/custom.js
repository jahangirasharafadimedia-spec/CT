// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdown items
    const dropdownItems = document.querySelectorAll('.nav-item.dropdown');

    // Add click event to each dropdown toggle
    dropdownItems.forEach(function(item) {
        const toggle = item.querySelector('.dropdown-toggle');
        
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Close all other dropdowns
            dropdownItems.forEach(function(otherItem) {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            item.classList.toggle('active');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item.dropdown')) {
            dropdownItems.forEach(function(item) {
                item.classList.remove('active');
            });
        }
    });

    // News Ticker Scrolling with constant speed
    const tickerContent = document.querySelector('.ticker-content');
    const tickerWrapper = document.querySelector('.ticker-wrapper');
    
    if (tickerContent && tickerWrapper) {
        // Constant scroll speed (pixels per second)
        const SCROLL_SPEED = 50; // Adjust this value to change speed
        
        let position = 0;
        let animationFrameId;
        let lastTime = performance.now();
        
        // Clone content for seamless loop
        const originalContent = tickerContent.innerHTML;
        tickerContent.innerHTML = originalContent + originalContent;
        
        function animate(currentTime) {
            const deltaTime = (currentTime - lastTime) / 1000; // Convert to seconds
            lastTime = currentTime;
            
            // Calculate new position
            position -= SCROLL_SPEED * deltaTime;
            
            // Get the width of half the content (since we duplicated it)
            const contentWidth = tickerContent.scrollWidth / 2;
            
            // Reset position when we've scrolled one full set
            if (Math.abs(position) >= contentWidth) {
                position = 0;
            }
            
            // Apply transform
            tickerContent.style.transform = `translateX(${position}px)`;
            
            animationFrameId = requestAnimationFrame(animate);
        }
        
        // Start animation
        animationFrameId = requestAnimationFrame(animate);
        
        // Pause on hover
        tickerWrapper.addEventListener('mouseenter', function() {
            cancelAnimationFrame(animationFrameId);
        });
        
        // Resume on mouse leave
        tickerWrapper.addEventListener('mouseleave', function() {
            lastTime = performance.now();
            animationFrameId = requestAnimationFrame(animate);
        });
    }

    // Initialize TOP EVENTS Swiper
    const topEventsSwiper = new Swiper('.top-events-swiper', {
        slidesPerView: 5,
        spaceBetween: 25,
        loop: true,
        navigation: {
            nextEl: '.top-events-next',
            prevEl: '.top-events-prev',
        },
        breakpoints: {
            0: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 25,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 25,
            },
            1200: {
                slidesPerView: 4,
                spaceBetween: 25,
            }
        }
    });

    // Initialize PERSPECTIVE Swiper
    const perspectiveSwiper = new Swiper('.perspective-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 25,
        loop: false,
        navigation: {
            nextEl: '.perspective-next',
            prevEl: '.perspective-prev',
        },
        on: {
            init: function() {
                // Hide left arrow on first slide
                if (this.isBeginning) {
                    this.navigation.prevEl.classList.add('swiper-button-disabled');
                }
                // Hide right arrow on last slide
                if (this.isEnd) {
                    this.navigation.nextEl.classList.add('swiper-button-disabled');
                }
            },
            slideChange: function() {
                // Show/hide arrows based on position
                if (this.isBeginning) {
                    this.navigation.prevEl.classList.add('swiper-button-disabled');
                } else {
                    this.navigation.prevEl.classList.remove('swiper-button-disabled');
                }
                
                if (this.isEnd) {
                    this.navigation.nextEl.classList.add('swiper-button-disabled');
                } else {
                    this.navigation.nextEl.classList.remove('swiper-button-disabled');
                }
            }
        },
        breakpoints: {
            0: {
                slidesPerView: 2,
                spaceBetween: 15,
            },
            480: {
                slidesPerView: 1.5,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2.5,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 3.5,
                spaceBetween: 25,
            },
            1200: {
                slidesPerView: 4,
                spaceBetween: 15,
            }
        }
    });

    // Hamburger Menu Toggle
    const hamburgerIcon = document.querySelector('.hamburger-menu-icon');
    const sidebarMenu = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarClose = document.querySelector('.sidebar-close');

    function openSidebar() {
        sidebarMenu.classList.add('active');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebarMenu.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburgerIcon) {
        hamburgerIcon.addEventListener('click', function(e) {
            e.preventDefault();
            openSidebar();
        });
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    }

    // Sidebar Dropdown Functionality
    const sidebarDropdownToggles = document.querySelectorAll('.sidebar-dropdown-toggle');
    
    sidebarDropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.closest('.sidebar-menu-item-dropdown');
            const isActive = dropdown.classList.contains('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.sidebar-menu-item-dropdown').forEach(function(item) {
                item.classList.remove('active');
            });
            
            // Toggle current dropdown
            if (!isActive) {
                dropdown.classList.add('active');
            }
        });
    });

    // Search Sidebar Toggle
    const searchIcon = document.querySelector('.search-icon');
    const searchSidebar = document.getElementById('searchSidebar');
    const searchSidebarOverlay = document.getElementById('searchSidebarOverlay');
    const searchSidebarClose = document.querySelector('.search-sidebar-close');

    function openSearchSidebar() {
        searchSidebar.classList.add('active');
        searchSidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSearchSidebar() {
        searchSidebar.classList.remove('active');
        searchSidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (searchIcon) {
        searchIcon.addEventListener('click', function(e) {
            e.preventDefault();
            openSearchSidebar();
        });
    }

    if (searchSidebarClose) {
        searchSidebarClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeSearchSidebar();
        });
    }

    if (searchSidebarOverlay) {
        searchSidebarOverlay.addEventListener('click', function() {
            closeSearchSidebar();
        });
    }

    // Sticky Header Container Functionality
    const headerContainer = document.querySelector('.header-container');
    
    if (headerContainer) {
        let lastScrollTop = 0;
        let ticking = false;
        const headerHeight = headerContainer.offsetHeight;
        
        function handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 0) {
                headerContainer.classList.add('sticky-active');
                // Add padding to body to prevent content jump
                document.body.style.paddingTop = headerHeight + 'px';
            } else {
                headerContainer.classList.remove('sticky-active');
                document.body.style.paddingTop = '0';
            }
            
            // Hide/show on scroll down/up (optional)
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down - hide header
                headerContainer.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up - show header
                headerContainer.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(handleScroll);
                ticking = true;
            }
        }, { passive: true });
        
        // Initial check
        handleScroll();
    }
});
