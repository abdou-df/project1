/**
 * Auto Care Garage - Main JavaScript
 * Enhanced with modern features and animations
 */

document.addEventListener("DOMContentLoaded", () => {
  // Initialize header scroll effect
  initHeaderScroll()

  // Initialize mobile menu
  initMobileMenu()

  // Initialize testimonials slider
  initTestimonialsSlider()

  // Initialize service card animations
  initServiceCards()

  // Initialize AOS animations
  initAOS()

  // Initialize typed.js for hero section
  initTypedJS()
})

/**
 * Header scroll effect
 */
function initHeaderScroll() {
  const header = document.querySelector(".header")

  window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
      header.classList.add("scrolled")
    } else {
      header.classList.remove("scrolled")
    }
  })
}

/**
 * Mobile menu functionality
 */
function initMobileMenu() {
  const menuToggle = document.querySelector(".mobile-menu-toggle")
  const mobileMenu = document.querySelector(".mobile-menu")

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener("click", () => {
      mobileMenu.classList.toggle("active")
      document.body.classList.toggle("menu-open")
    })
  }
}

/**
 * Testimonials slider with auto-scroll
 */
function initTestimonialsSlider() {
  const slider = document.querySelector(".testimonials-slider")

  if (!slider) return

  // Auto scroll functionality
  let scrollAmount = 0
  const testimonialWidth = 380 // Width of testimonial + gap
  const maxScroll = slider.scrollWidth - slider.clientWidth

  // Manual scroll with mouse wheel
  slider.addEventListener("wheel", (e) => {
    e.preventDefault()
    slider.scrollLeft += e.deltaY
  })

  // Auto scroll interval
  setInterval(() => {
    scrollAmount += 1
    if (scrollAmount >= maxScroll) {
      scrollAmount = 0
      slider.scrollTo({ left: 0, behavior: "smooth" })
    } else {
      slider.scrollTo({ left: scrollAmount, behavior: "smooth" })
    }
  }, 50)
}

/**
 * Service card animations
 */
function initServiceCards() {
  const serviceCards = document.querySelectorAll(".service-card")

  serviceCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-10px)"
      this.style.boxShadow = "var(--box-shadow-lg)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)"
      this.style.boxShadow = "var(--box-shadow)"
    })
  })
}

/**
 * Initialize AOS (Animate On Scroll)
 */
function initAOS() {
  // Check if AOS is loaded
  if (typeof AOS !== "undefined") {
    AOS.init({
      duration: 800,
      easing: "ease-in-out",
      once: true,
      offset: 100,
    })
  }
}

/**
 * Initialize Typed.js for dynamic text animation
 */
function initTypedJS() {
  // Check if element exists and if Typed is loaded
  const heroSubtitle = document.querySelector(".hero-subtitle")

  if (heroSubtitle && typeof Typed !== "undefined") {
    new Typed(heroSubtitle, {
      strings: [
        "Trust your vehicle with our expert mechanics",
        "Quality auto repair services at affordable prices",
        "Experienced technicians for all vehicle makes and models",
      ],
      typeSpeed: 50,
      backSpeed: 30,
      backDelay: 2000,
      loop: true,
    })
  }
}

/**
 * Smooth scroll to sections
 */
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault()

    const target = document.querySelector(this.getAttribute("href"))
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
      })
    }
  })
})

/**
 * Add counter animation to statistics
 */
function animateCounter(el) {
  const target = Number.parseInt(el.getAttribute("data-count"))
  const duration = 2000
  const step = (target / duration) * 10
  let current = 0

  const timer = setInterval(() => {
    current += step
    el.textContent = Math.round(current)

    if (current >= target) {
      el.textContent = target
      clearInterval(timer)
    }
  }, 10)
}

// Animate counters when they come into view
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const counters = entry.target.querySelectorAll(".counter")
        counters.forEach((counter) => {
          animateCounter(counter)
        })
        observer.unobserve(entry.target)
      }
    })
  },
  {
    threshold: 0.5,
  },
)

// Observe stats section if it exists
const statsSection = document.querySelector(".stats-section")
if (statsSection) {
  observer.observe(statsSection)
}
