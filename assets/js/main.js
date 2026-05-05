/**
 * Employee Skill Tracker - Main JavaScript
 * Interactive features and animations
 */

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initTabs();
    initModals();
    initAnimations();
    initTooltips();
    initDropdowns();
    initAssessmentTimer();
    initSkillTags();
    initFormValidation();
    initPasswordStrength();
    initCharts();
});

// ==================== PASSWORD STRENGTH INDICATOR ====================
function initPasswordStrength() {
    const passwordInputs = document.querySelectorAll('input[data-password-strength]');
    
    passwordInputs.forEach(input => {
        const indicatorId = input.dataset.passwordStrength;
        const indicator = document.getElementById(indicatorId);
        
        if (!indicator) return;
        
        input.addEventListener('input', function() {
            updatePasswordStrength(this.value, indicator);
        });
    });
}

function updatePasswordStrength(password, indicator) {
    const bar = indicator.querySelector('.password-strength-fill');
    const text = indicator.querySelector('.password-strength-label');
    const requirements = indicator.querySelector('.password-requirements');
    
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    // Calculate strength
    if (checks.length) strength += 20;
    if (checks.uppercase) strength += 20;
    if (checks.lowercase) strength += 20;
    if (checks.number) strength += 20;
    if (checks.special) strength += 20;
    
    // Update bar
    bar.style.width = strength + '%';
    bar.className = 'password-strength-fill';
    
    // Update text
    let label = 'Weak';
    let className = 'weak';
    
    if (strength >= 80) {
        label = 'Strong';
        className = 'strong';
    } else if (strength >= 40) {
        label = 'Medium';
        className = 'medium';
    }
    
    bar.classList.add(className);
    text.textContent = label;
    text.className = 'password-strength-label ' + className;
    
    // Update requirements list
    if (requirements) {
        const reqList = requirements.querySelector('ul');
        if (reqList) {
            reqList.innerHTML = `
                <li class="${checks.length ? 'met' : ''}">At least 8 characters</li>
                <li class="${checks.uppercase ? 'met' : ''}">One uppercase letter</li>
                <li class="${checks.lowercase ? 'met' : ''}">One lowercase letter</li>
                <li class="${checks.number ? 'met' : ''}">One number</li>
                <li class="${checks.special ? 'met' : ''}">One special character</li>
            `;
        }
    }
}

// ==================== SIDEBAR ====================
function initSidebar() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarClose = document.querySelector('.sidebar-close');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }
    
    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', () => {
            sidebar.classList.remove('open');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 1024 && sidebar && !sidebar.contains(e.target) && !sidebarToggle?.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}

// ==================== TABS ====================
function initTabs() {
    const tabContainers = document.querySelectorAll('.tabs');
    
    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetId = tab.dataset.tab;
                
                // Remove active from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active to clicked tab
                tab.classList.add('active');
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    });
}

// ==================== MODALS ====================
function initModals() {
    // Open modal
    document.querySelectorAll('[data-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.dataset.modal;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Close modal
    document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
        el.addEventListener('click', (e) => {
            if (e.target === el || el.classList.contains('modal-close')) {
                const modal = el.closest('.modal-overlay');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
    });
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });
}

// Open modal function (global)
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// Close modal function (global)
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// ==================== ANIMATIONS ====================
function initAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements with animation classes
    document.querySelectorAll('.glass-card, .stat-card, .project-card, .dashboard-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
    
    // Add animate-in class style
    const style = document.createElement('style');
    style.textContent = `
        .animate-in {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
    `;
    document.head.appendChild(style);
    
    // Counter animation for stats
    animateCounters();
}

// Counter animation
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(counter);
                }
            });
        });
        
        observer.observe(counter);
    });
}

// ==================== TOOLTIPS ====================
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', (e) => {
            const tooltipText = el.dataset.tooltip;
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: fixed;
                background: rgba(15, 23, 42, 0.95);
                color: #fff;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 12px;
                z-index: 9999;
                pointer-events: none;
                white-space: nowrap;
                border: 1px solid rgba(255,255,255,0.1);
            `;
            document.body.appendChild(tooltip);
            
            const rect = el.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            
            el._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', () => {
            if (el._tooltip) {
                el._tooltip.remove();
                delete el._tooltip;
            }
        });
    });
}

// ==================== DROPDOWNS ====================
function initDropdowns() {
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
            });
            
            document.addEventListener('click', () => {
                menu.classList.remove('show');
            });
        }
    });
}

// ==================== ASSESSMENT TIMER ====================
function initAssessmentTimer() {
    const timerElement = document.getElementById('assessment-timer');
    if (!timerElement) return;
    
    const duration = parseInt(timerElement.dataset.duration) * 60; // Convert to seconds
    let remaining = duration;
    
    const updateTimer = () => {
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        const timeDisplay = timerElement.querySelector('.time-display');
        if (timeDisplay) {
            timeDisplay.textContent = display;
        }
        
        // Warning when less than 5 minutes
        if (remaining <= 300) {
            timerElement.classList.add('warning');
        }
        
        // Auto-submit when time is up
        if (remaining <= 0) {
            clearInterval(timerInterval);
            autoSubmitAssessment();
        }
        
        remaining--;
    };
    
    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
}

function autoSubmitAssessment() {
    const form = document.getElementById('assessment-form');
    if (form) {
        // Add hidden field to indicate auto-submit
        const autoSubmitField = document.createElement('input');
        autoSubmitField.type = 'hidden';
        autoSubmitField.name = 'auto_submitted';
        autoSubmitField.value = '1';
        form.appendChild(autoSubmitField);
        form.submit();
    }
}

// ==================== SKILL TAGS ====================
function initSkillTags() {
    // Remove skill tag
    document.querySelectorAll('.skill-tag .remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const tag = this.closest('.skill-tag');
            tag.style.transform = 'scale(0)';
            setTimeout(() => tag.remove(), 200);
        });
    });
    
    // Add skill from select
    const skillSelect = document.getElementById('skill-select');
    const addSkillBtn = document.getElementById('add-skill-btn');
    const skillsContainer = document.getElementById('skills-container');
    
    if (skillSelect && addSkillBtn && skillsContainer) {
        addSkillBtn.addEventListener('click', () => {
            const skillId = skillSelect.value;
            const skillName = skillSelect.options[skillSelect.selectedIndex].text;
            const proficiency = document.getElementById('proficiency-select')?.value || 'beginner';
            
            if (skillId) {
                addSkillTag(skillsContainer, skillId, skillName, proficiency);
                skillSelect.value = '';
            }
        });
    }
}

function addSkillTag(container, skillId, skillName, proficiency) {
    // Check if skill already exists
    if (container.querySelector(`[data-skill-id="${skillId}"]`)) {
        showNotification('Skill already added', 'warning');
        return;
    }
    
    const tag = document.createElement('div');
    tag.className = 'skill-tag';
    tag.dataset.skillId = skillId;
    tag.innerHTML = `
        ${skillName}
        <span class="skill-level ${proficiency}">${proficiency}</span>
        <input type="hidden" name="skills[]" value="${skillId}">
        <input type="hidden" name="proficiency[]" value="${proficiency}">
        <span class="remove" onclick="removeSkillTag(this)">&times;</span>
    `;
    
    container.appendChild(tag);
    
    // Animate in
    tag.style.opacity = '0';
    tag.style.transform = 'scale(0.8)';
    setTimeout(() => {
        tag.style.transition = 'all 0.2s ease';
        tag.style.opacity = '1';
        tag.style.transform = 'scale(1)';
    }, 10);
}

function removeSkillTag(btn) {
    const tag = btn.closest('.skill-tag');
    tag.style.transform = 'scale(0)';
    tag.style.opacity = '0';
    setTimeout(() => tag.remove(), 200);
}

// ==================== FORM VALIDATION ====================
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            // Remove existing error messages
            form.querySelectorAll('.error-message').forEach(el => el.remove());
            form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    field.style.borderColor = 'var(--danger-color)';
                    
                    // Add error message
                    const errorMsg = document.createElement('span');
                    errorMsg.className = 'error-message';
                    errorMsg.style.cssText = 'color: var(--danger-color); font-size: 12px; margin-top: 4px; display: block;';
                    errorMsg.textContent = 'This field is required';
                    field.parentNode.insertBefore(errorMsg, field.nextSibling);
                }
            });
            
            // Password match validation
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');
            
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                confirmPassword.classList.add('error');
                confirmPassword.style.borderColor = 'var(--danger-color)';
                
                const errorMsg = document.createElement('span');
                errorMsg.className = 'error-message';
                errorMsg.style.cssText = 'color: var(--danger-color); font-size: 12px; margin-top: 4px; display: block;';
                errorMsg.textContent = 'Passwords do not match';
                confirmPassword.parentNode.insertBefore(errorMsg, confirmPassword.nextSibling);
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

// ==================== CHARTS ====================
function initCharts() {
    // Simple CSS-based charts
    createBarCharts();
    createPieCharts();
    createLineCharts();
}

function createBarCharts() {
    document.querySelectorAll('.chart-bar').forEach(chart => {
        const data = JSON.parse(chart.dataset.values || '[]');
        const labels = JSON.parse(chart.dataset.labels || '[]');
        const max = Math.max(...data);
        
        chart.innerHTML = '';
        chart.style.cssText = `
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 200px;
            padding: 20px;
        `;
        
        data.forEach((value, index) => {
            const bar = document.createElement('div');
            const height = (value / max) * 100;
            bar.style.cssText = `
                flex: 1;
                height: ${height}%;
                background: var(--gradient-primary);
                border-radius: 4px 4px 0 0;
                position: relative;
                transition: height 0.5s ease;
                cursor: pointer;
            `;
            bar.innerHTML = `
                <div style="
                    position: absolute;
                    bottom: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    margin-bottom: 5px;
                    font-size: 12px;
                    font-weight: 600;
                ">${value}</div>
                <div style="
                    position: absolute;
                    top: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    margin-top: 5px;
                    font-size: 11px;
                    color: var(--text-muted);
                    white-space: nowrap;
                ">${labels[index] || ''}</div>
            `;
            chart.appendChild(bar);
        });
    });
}

function createPieCharts() {
    document.querySelectorAll('.chart-pie').forEach(chart => {
        const data = JSON.parse(chart.dataset.values || '[]');
        const colors = ['#6366f1', '#ec4899', '#06b6d4', '#10b981', '#f59e0b'];
        const total = data.reduce((a, b) => a + b, 0);
        
        let currentAngle = 0;
        const segments = data.map((value, index) => {
            const angle = (value / total) * 360;
            const startAngle = currentAngle;
            currentAngle += angle;
            return { value, angle, startAngle, color: colors[index % colors.length] };
        });
        
        // Create conic gradient
        const gradient = segments.map(s => 
            `${s.color} ${s.startAngle}deg ${s.startAngle + s.angle}deg`
        ).join(', ');
        
        chart.style.cssText = `
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(${gradient});
            position: relative;
        `;
        
        // Add center hole for donut effect
        const hole = document.createElement('div');
        hole.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 60%;
            background: var(--bg-dark);
            border-radius: 50%;
        `;
        chart.appendChild(hole);
    });
}

function createLineCharts() {
    document.querySelectorAll('.chart-line').forEach(chart => {
        const data = JSON.parse(chart.dataset.values || '[]');
        const max = Math.max(...data);
        const min = Math.min(...data);
        const range = max - min || 1;
        
        const points = data.map((value, index) => {
            const x = (index / (data.length - 1)) * 100;
            const y = 100 - ((value - min) / range) * 100;
            return `${x},${y}`;
        }).join(' ');
        
        chart.innerHTML = `
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                <polyline
                    fill="none"
                    stroke="url(#lineGradient)"
                    stroke-width="2"
                    points="${points}"
                />
                <defs>
                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#6366f1" />
                        <stop offset="100%" style="stop-color:#ec4899" />
                    </linearGradient>
                </defs>
            </svg>
        `;
    });
}

// ==================== NOTIFICATIONS ====================
function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification-toast').forEach(el => el.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification-toast alert alert-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
    `;
    notification.style.cssText = `
        position: fixed;
        top: 90px;
        right: 30px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        min-width: 300px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// ==================== AJAX HELPERS ====================
function ajaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        if (method === 'POST' && data) {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
        
        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    resolve(JSON.parse(xhr.responseText));
                } catch {
                    resolve(xhr.responseText);
                }
            } else {
                reject(xhr.statusText);
            }
        };
        
        xhr.onerror = () => reject(xhr.statusText);
        
        if (data && typeof data === 'object') {
            const params = new URLSearchParams(data).toString();
            xhr.send(params);
        } else {
            xhr.send(data);
        }
    });
}

// ==================== CONFIRM DIALOG ====================
function confirmAction(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay active';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Confirm Action</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                <button class="btn btn-danger" id="confirm-btn">Confirm</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.querySelector('#confirm-btn').addEventListener('click', () => {
        callback();
        modal.remove();
    });
}

// ==================== LOADING OVERLAY ====================
function showLoading(message = 'Loading...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="spinner"></div>
        <p>${message}</p>
    `;
    document.body.appendChild(overlay);
    return overlay;
}

function hideLoading(overlay) {
    if (overlay) {
        overlay.remove();
    }
}

// ==================== PASSWORD TOGGLE ====================
function togglePassword(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);
    
    if (input && toggle) {
        toggle.addEventListener('click', () => {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            toggle.classList.toggle('fa-eye');
            toggle.classList.toggle('fa-eye-slash');
        });
    }
}

// ==================== EXPORT TO CSV ====================
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = '';
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = Array.from(cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(',');
        csv += rowData + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ==================== SMOOTH SCROLL ====================
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ==================== DEBOUNCE ====================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ==================== THROTTLE ====================
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}
