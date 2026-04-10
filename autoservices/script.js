// Лоадер
window.addEventListener('load', () => {
    const loader = document.querySelector('.loader');
    setTimeout(() => {
        loader.classList.add('loader-hidden');
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    }, 1000);
});

// Навигация при скролле
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Мобильное меню
const menuToggle = document.querySelector('.menu-toggle');
const navMenu = document.querySelector('.nav-menu');

menuToggle.addEventListener('click', () => {
    navMenu.classList.toggle('active');
    menuToggle.innerHTML = navMenu.classList.contains('active') 
        ? '<i class="fas fa-times"></i>' 
        : '<i class="fas fa-bars"></i>';
});

// Анимация появления при скролле
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animated');
        }
    });
}, observerOptions);

// Наблюдаем за элементами
document.querySelectorAll('.animate-on-scroll').forEach(el => {
    observer.observe(el);
});

// Анимация чисел
const statNumbers = document.querySelectorAll('.stat-number');
const animateNumbers = () => {
    statNumbers.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                stat.textContent = target + '+';
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current);
            }
        }, 16);
    });
};

// Запуск анимации чисел при скролле до секции
const aboutSection = document.querySelector('.about');
const aboutObserver = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
        animateNumbers();
        aboutObserver.unobserve(aboutSection);
    }
}, { threshold: 0.5 });

aboutObserver.observe(aboutSection);

// Слайдер изображений
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
}

setInterval(() => {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}, 3000);

// Плавная прокрутка
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
            
            // Закрываем мобильное меню
            navMenu.classList.remove('active');
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
});

// Эффект параллакса
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const parallaxElements = document.querySelectorAll('.hero::before');
    
    parallaxElements.forEach(element => {
        const speed = element.dataset.speed || 0.5;
        element.style.transform = `translateY(${scrolled * speed}px)`;
    });
});

// Модальные окна для записи
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация модальных окон
    const bookingModal = document.getElementById('bookingModal');
    const loginRequiredModal = document.getElementById('loginRequiredModal');
    const modalCloses = document.querySelectorAll('.modal-close');
    const bookButtons = document.querySelectorAll('.btn-service');
    
    console.log('Найдено кнопок "Записаться":', bookButtons.length);
    
    // Открытие модального окна записи
    bookButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Кнопка "Записаться" нажата');
            
            const serviceId = this.getAttribute('data-service-id');
            const serviceName = this.getAttribute('data-service-name');
            
            console.log('ID услуги:', serviceId, 'Название:', serviceName);
            
            // Проверяем авторизацию через JavaScript переменную
            if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
                console.log('Пользователь авторизован');
                openBookingModal(serviceId, serviceName);
            } else {
                console.log('Пользователь НЕ авторизован');
                openLoginRequiredModal();
            }
        });
    });
    
    // Функция открытия модального окна записи
    window.openBookingModal = function(serviceId, serviceName) {
        console.log('Открываем модалку записи для услуги:', serviceName);
        
        document.getElementById('modalServiceId').value = serviceId;
        document.getElementById('modalServiceName').textContent = serviceName;
        
        // Установка минимальной даты (сегодня)
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('bookingDate');
        if (dateInput) {
            dateInput.min = today;
            dateInput.value = today;
        }
        
        // Установка CSRF токена
        const csrfInput = document.querySelector('input[name="csrf_token"]');
        if (csrfInput && typeof csrfToken !== 'undefined') {
            csrfInput.value = csrfToken;
        }
        
        if (bookingModal) {
            bookingModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            console.log('Модальное окно показано');
        } else {
            console.error('Модальное окно bookingModal не найдено!');
        }
    };
    
    // Функция открытия модального окна авторизации
    window.openLoginRequiredModal = function() {
        console.log('Открываем модалку авторизации');
        
        if (loginRequiredModal) {
            loginRequiredModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Модальное окно loginRequiredModal не найдено!');
        }
    };
    
    // Закрытие модальных окон
    modalCloses.forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Закрытие по клику на фон
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Закрытие по ESC
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        }
    });
    
    // Валидация формы записи
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(event) {
            const date = document.getElementById('bookingDate')?.value;
            const time = document.getElementById('bookingTime')?.value;
            
            if (!date || !time) {
                event.preventDefault();
                showAlert('Пожалуйста, выберите дату и время', 'error');
                return;
            }
            
            // Проверка на прошедшую дату
            const selectedDate = new Date(date + 'T' + time);
            const now = new Date();
            
            if (selectedDate < now) {
                event.preventDefault();
                showAlert('Нельзя записываться на прошедшее время', 'error');
                return;
            }
            
            console.log('Форма записи отправляется');
        });
    }
    
    // Функция показа уведомлений
    window.showAlert = function(message, type = 'info') {
        console.log('Показываем уведомление:', type, message);
        
        // Удаляем старые уведомления
        const oldAlerts = document.querySelectorAll('.alert');
        oldAlerts.forEach(alert => alert.remove());
        
        // Создаем новое уведомление
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        
        // Иконка в зависимости от типа
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        
        alertDiv.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Автоматическое удаление через 5 секунд
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    };
});