/**
 * Auto Care Garage - Main JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const mobileMenu = document.querySelector(".mobile-menu")

  if (mobileMenuToggle && mobileMenu) {
    mobileMenuToggle.addEventListener("click", () => {
      mobileMenu.classList.toggle("active")
      mobileMenuToggle.querySelector("i").classList.toggle("fa-bars")
      mobileMenuToggle.querySelector("i").classList.toggle("fa-times")
    })
  }

  // Testimonials slider
  const testimonialsSlider = document.querySelector(".testimonials-slider")
  if (testimonialsSlider) {
    let isDown = false
    let startX
    let scrollLeft

    testimonialsSlider.addEventListener("mousedown", (e) => {
      isDown = true
      testimonialsSlider.classList.add("active")
      startX = e.pageX - testimonialsSlider.offsetLeft
      scrollLeft = testimonialsSlider.scrollLeft
    })

    testimonialsSlider.addEventListener("mouseleave", () => {
      isDown = false
      testimonialsSlider.classList.remove("active")
    })

    testimonialsSlider.addEventListener("mouseup", () => {
      isDown = false
      testimonialsSlider.classList.remove("active")
    })

    testimonialsSlider.addEventListener("mousemove", (e) => {
      if (!isDown) return
      e.preventDefault()
      const x = e.pageX - testimonialsSlider.offsetLeft
      const walk = (x - startX) * 2
      testimonialsSlider.scrollLeft = scrollLeft - walk
    })
  }

  // Password toggle
  const passwordToggles = document.querySelectorAll(".password-toggle")

  if (passwordToggles.length > 0) {
    passwordToggles.forEach((toggle) => {
      toggle.addEventListener("click", function () {
        const passwordInput = this.previousElementSibling
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password"
        passwordInput.setAttribute("type", type)
        this.querySelector("i").classList.toggle("fa-eye")
        this.querySelector("i").classList.toggle("fa-eye-slash")
      })
    })
  }

  // Form validation
  const forms = document.querySelectorAll("form")

  if (forms.length > 0) {
    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        let valid = true
        const requiredInputs = form.querySelectorAll("[required]")

        requiredInputs.forEach((input) => {
          if (!input.value.trim()) {
            valid = false
            input.classList.add("error")

            // Create error message if it doesn't exist
            let errorMessage = input.nextElementSibling
            if (!errorMessage || !errorMessage.classList.contains("error-message")) {
              errorMessage = document.createElement("div")
              errorMessage.classList.add("error-message")
              errorMessage.textContent = "This field is required"
              input.parentNode.insertBefore(errorMessage, input.nextSibling)
            }
          } else {
            input.classList.remove("error")

            // Remove error message if it exists
            const errorMessage = input.nextElementSibling
            if (errorMessage && errorMessage.classList.contains("error-message")) {
              errorMessage.remove()
            }
          }
        })

        if (!valid) {
          e.preventDefault()
        }
      })
    })
  }

  // Alerts auto-close
  const alerts = document.querySelectorAll(".alert")

  if (alerts.length > 0) {
    alerts.forEach((alert) => {
      setTimeout(() => {
        alert.style.opacity = "0"
        setTimeout(() => {
          alert.remove()
        }, 500)
      }, 5000)
    })
  }

  // Smooth scroll for anchor links
  const anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])')

  if (anchorLinks.length > 0) {
    anchorLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault()

        const targetId = this.getAttribute("href")
        const targetElement = document.querySelector(targetId)

        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 100,
            behavior: "smooth",
          })
        }
      })
    })
  }

  // Date picker min date (today)
  const dateInputs = document.querySelectorAll('input[type="date"]')

  if (dateInputs.length > 0) {
    const today = new Date().toISOString().split("T")[0]

    dateInputs.forEach((input) => {
      if (!input.min) {
        input.min = today
      }
    })
  }
})

