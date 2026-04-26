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

    // TOP EVENTS Swiper — one instance per section (widget-safe)
    document.querySelectorAll('.top-events-section').forEach(function (section) {
        const swiperEl = section.querySelector('.top-events-swiper');
        if (!swiperEl || swiperEl.swiper) {
            return;
        }
        const nextEl = section.querySelector('.top-events-next');
        const prevEl = section.querySelector('.top-events-prev');
        if (!nextEl || !prevEl) {
            return;
        }
        new Swiper(swiperEl, {
            slidesPerView: 5,
            spaceBetween: 25,
            loop: true,
            navigation: {
                nextEl: nextEl,
                prevEl: prevEl,
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
    });

    // Initialize PERSPECTIVE Swiper (one instance per .perspective-section — supports widget + static markup)
    document.querySelectorAll('.perspective-section').forEach(function (section) {
        const swiperEl = section.querySelector('.perspective-swiper');
        if (!swiperEl || swiperEl.swiper) {
            return;
        }
        const nextEl = section.querySelector('.perspective-next');
        const prevEl = section.querySelector('.perspective-prev');
        if (!nextEl || !prevEl) {
            return;
        }
        new Swiper(swiperEl, {
            slidesPerView: 'auto',
            spaceBetween: 25,
            loop: false,
            navigation: {
                nextEl: nextEl,
                prevEl: prevEl,
            },
            on: {
                init: function () {
                    if (this.isBeginning && this.navigation.prevEl) {
                        this.navigation.prevEl.classList.add('swiper-button-disabled');
                    }
                    if (this.isEnd && this.navigation.nextEl) {
                        this.navigation.nextEl.classList.add('swiper-button-disabled');
                    }
                },
                slideChange: function () {
                    if (this.navigation.prevEl) {
                        if (this.isBeginning) {
                            this.navigation.prevEl.classList.add('swiper-button-disabled');
                        } else {
                            this.navigation.prevEl.classList.remove('swiper-button-disabled');
                        }
                    }
                    if (this.navigation.nextEl) {
                        if (this.isEnd) {
                            this.navigation.nextEl.classList.add('swiper-button-disabled');
                        } else {
                            this.navigation.nextEl.classList.remove('swiper-button-disabled');
                        }
                    }
                },
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
                },
            },
        });
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
        const liveIn = document.getElementById('liveSearchInput');
        if (liveIn) {
            setTimeout(function () {
                liveIn.focus();
            }, 100);
        }
    }

    function closeSearchSidebar() {
        searchSidebar.classList.remove('active');
        searchSidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
        const liveIn = document.getElementById('liveSearchInput');
        const liveOut = document.getElementById('liveSearchResults');
        if (liveIn) {
            liveIn.value = '';
        }
        if (liveOut) {
            liveOut.innerHTML = '';
        }
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

    // Live search (header panel): 3+ chars, matches title, content, excerpt, ACF/post meta.
    if (typeof communicationstodaySearch !== 'undefined') {
        const liveSearchInput = document.getElementById('liveSearchInput');
        const liveSearchResults = document.getElementById('liveSearchResults');
        let liveSearchTimer = null;

        function setLiveSearchMessage(text) {
            if (!liveSearchResults) {
                return;
            }
            if (!text) {
                liveSearchResults.innerHTML = '';
                return;
            }
            liveSearchResults.innerHTML = '<p class="search-results-empty">' + text + '</p>';
        }

        function runLiveSearch(term) {
            if (!liveSearchResults) {
                return;
            }
            setLiveSearchMessage(communicationstodaySearch.i18n.loading);

            const body = new URLSearchParams();
            body.append('action', 'communicationstoday_live_search');
            body.append('nonce', communicationstodaySearch.nonce);
            body.append('term', term);

            fetch(communicationstodaySearch.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString(),
                credentials: 'same-origin',
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data || !data.success) {
                        setLiveSearchMessage(communicationstodaySearch.i18n.error);
                        return;
                    }
                    if (data.data.too_short) {
                        setLiveSearchMessage(communicationstodaySearch.i18n.minChars);
                        return;
                    }
                    liveSearchResults.innerHTML = data.data.html || '';
                })
                .catch(function () {
                    setLiveSearchMessage(communicationstodaySearch.i18n.error);
                });
        }

        if (liveSearchInput && liveSearchResults) {
            liveSearchInput.addEventListener('input', function () {
                const term = liveSearchInput.value.trim();
                clearTimeout(liveSearchTimer);
                if (term.length === 0) {
                    liveSearchResults.innerHTML = '';
                    return;
                }
                if (term.length < 3) {
                    setLiveSearchMessage(communicationstodaySearch.i18n.minChars);
                    return;
                }
                liveSearchTimer = setTimeout(function () {
                    runLiveSearch(term);
                }, 350);
            });

        }
    }

    // Archive listing "More posts" button (AJAX append).
    if (typeof communicationstodayArchiveLoadMore !== 'undefined') {
        const moreBtn = document.querySelector('.archive-load-more-button');
        const postList = document.getElementById('archive-post-list');
        if (moreBtn && postList) {
            moreBtn.addEventListener('click', function () {
                const page = parseInt(moreBtn.getAttribute('data-page') || '1', 10);
                const maxPages = parseInt(moreBtn.getAttribute('data-max-pages') || '1', 10);
                const nextPage = page + 1;
                if (nextPage > maxPages) {
                    moreBtn.closest('.archive-load-more-wrap')?.remove();
                    return;
                }

                const queryVars = moreBtn.getAttribute('data-query-vars') || '{}';
                moreBtn.disabled = true;
                moreBtn.textContent = communicationstodayArchiveLoadMore.i18n.loading;

                const body = new URLSearchParams();
                body.append('action', 'communicationstoday_archive_load_more');
                body.append('nonce', communicationstodayArchiveLoadMore.nonce);
                body.append('page', String(nextPage));
                body.append('query_vars', queryVars);

                fetch(communicationstodayArchiveLoadMore.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString(),
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data || !data.success || !data.data || typeof data.data.html !== 'string') {
                            throw new Error('Invalid response');
                        }

                        if (data.data.html) {
                            const loadMoreWrap = moreBtn.closest('.archive-load-more-wrap');
                            if (loadMoreWrap) {
                                // Keep the button at the very end of list.
                                loadMoreWrap.insertAdjacentHTML('beforebegin', data.data.html);
                            } else {
                                postList.insertAdjacentHTML('beforeend', data.data.html);
                            }
                            moreBtn.setAttribute('data-page', String(nextPage));
                        }

                        if (!data.data.has_more || nextPage >= maxPages) {
                            moreBtn.closest('.archive-load-more-wrap')?.remove();
                            return;
                        }

                        moreBtn.disabled = false;
                        moreBtn.textContent = communicationstodayArchiveLoadMore.i18n.more;
                    })
                    .catch(function () {
                        moreBtn.disabled = false;
                        moreBtn.textContent = communicationstodayArchiveLoadMore.i18n.error;
                        setTimeout(function () {
                            moreBtn.textContent = communicationstodayArchiveLoadMore.i18n.more;
                        }, 1200);
                    });
            });
        }
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
