document.addEventListener("DOMContentLoaded", () => {
  // Form sections for multi-step registration
  const sections = document.querySelectorAll(".form-section")
  const progressBar = document.querySelector(".progress-bar")
  const steps = document.querySelectorAll(".step")
  let currentSection = 0

  // Initialize the form
  function initForm() {
    // Show the first section
    sections[0].classList.add("active")
    updateProgress()

    // Set up password strength meter
    const passwordInput = document.getElementById("password")
    const confirmPasswordInput = document.getElementById("confirm_password")
    const strengthIndicator = document.getElementById("password-strength")

    if (passwordInput) {
      passwordInput.addEventListener("input", function () {
        checkPasswordStrength(this.value, strengthIndicator)

        if (confirmPasswordInput.value) {
          checkPasswordMatch(passwordInput.value, confirmPasswordInput.value)
        }
      })
    }

    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener("input", function () {
        checkPasswordMatch(passwordInput.value, this.value)
      })
    }

    // Set up password visibility toggles
    const togglePassword = document.getElementById("togglePassword")
    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword")

    if (togglePassword) {
      togglePassword.addEventListener("click", function () {
        togglePasswordVisibility(passwordInput, this)
      })
    }

    if (toggleConfirmPassword) {
      toggleConfirmPassword.addEventListener("click", function () {
        togglePasswordVisibility(confirmPasswordInput, this)
      })
    }

    // Set up navigation buttons
    const nextButtons = document.querySelectorAll(".btn-next")
    const prevButtons = document.querySelectorAll(".btn-prev")

    nextButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault()
        if (validateSection(currentSection)) {
          goToNextSection()
        }
      })
    })

    prevButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault()
        goToPrevSection()
      })
    })

    // Form validation
    const form = document.getElementById("registrationForm")
    if (form) {
      form.addEventListener("submit", (e) => {
        if (!validateForm()) {
          e.preventDefault()
        }
      })
    }
  }

  // Password strength checker
  function checkPasswordStrength(password, indicator) {
    if (!indicator) return

    // Remove all classes
    indicator.classList.remove("strength-weak", "strength-medium", "strength-strong")

    if (password.length === 0) {
      indicator.style.width = "0"
      return
    }

    // Check password strength
    let strength = 0

    // Length check
    if (password.length >= 8) strength += 1

    // Complexity checks
    if (/[A-Z]/.test(password)) strength += 1
    if (/[0-9]/.test(password)) strength += 1
    if (/[^A-Za-z0-9]/.test(password)) strength += 1

    // Update indicator
    if (strength <= 2) {
      indicator.classList.add("strength-weak")
    } else if (strength === 3) {
      indicator.classList.add("strength-medium")
    } else {
      indicator.classList.add("strength-strong")
    }
  }

  // Check if passwords match
  function checkPasswordMatch(password, confirmPassword) {
    const confirmInput = document.getElementById("confirm_password")
    const matchFeedback = document.getElementById("password-match-feedback")

    if (!matchFeedback) return

    if (password === confirmPassword && password !== "") {
      matchFeedback.textContent = "Passwords match!"
      matchFeedback.className = "text-success small mt-1"
      confirmInput.classList.remove("is-invalid")
      confirmInput.classList.add("is-valid")
    } else if (confirmPassword !== "") {
      matchFeedback.textContent = "Passwords do not match"
      matchFeedback.className = "text-danger small mt-1"
      confirmInput.classList.remove("is-valid")
      confirmInput.classList.add("is-invalid")
    } else {
      matchFeedback.textContent = ""
      confirmInput.classList.remove("is-valid", "is-invalid")
    }
  }

  // Toggle password visibility
  function togglePasswordVisibility(input, icon) {
    const type = input.getAttribute("type") === "password" ? "text" : "password"
    input.setAttribute("type", type)

    // Toggle icon
    icon.classList.toggle("fa-eye")
    icon.classList.toggle("fa-eye-slash")
  }

  // Go to next section
  function goToNextSection() {
    if (currentSection < sections.length - 1) {
      sections[currentSection].classList.remove("active")
      currentSection++
      sections[currentSection].classList.add("active")
      updateProgress()
    }
  }

  // Go to previous section
  function goToPrevSection() {
    if (currentSection > 0) {
      sections[currentSection].classList.remove("active")
      currentSection--
      sections[currentSection].classList.add("active")
      updateProgress()
    }
  }

  // Update progress bar and steps
  function updateProgress() {
    const progress = ((currentSection + 1) / sections.length) * 100
    progressBar.style.width = `${progress}%`

    steps.forEach((step, index) => {
      if (index < currentSection) {
        step.classList.add("completed")
        step.classList.remove("active")
      } else if (index === currentSection) {
        step.classList.add("active")
        step.classList.remove("completed")
      } else {
        step.classList.remove("active", "completed")
      }
    })
  }

  // Validate current section
  function validateSection(sectionIndex) {
    const currentSectionEl = sections[sectionIndex]
    const inputs = currentSectionEl.querySelectorAll("input[required]")
    let isValid = true

    inputs.forEach((input) => {
      if (!input.value.trim()) {
        isValid = false
        showError(input, "This field is required")
      } else {
        removeError(input)

        // Additional validation based on input type
        if (input.type === "email" && !validateEmail(input.value)) {
          isValid = false
          showError(input, "Please enter a valid email address")
        } else if (input.id === "phone" && !validatePhone(input.value)) {
          isValid = false
          showError(input, "Please enter a valid phone number")
        }
      }
    })

    // Password specific validation
    if (sectionIndex === 1) {
      // Assuming passwords are in section 1
      const password = document.getElementById("password")
      const confirmPassword = document.getElementById("confirm_password")

      if (password && confirmPassword && password.value !== confirmPassword.value) {
        isValid = false
        showError(confirmPassword, "Passwords do not match")
      }
    }

    return isValid
  }

  // Validate the entire form
  function validateForm() {
    let isValid = true

    for (let i = 0; i < sections.length; i++) {
      if (!validateSection(i)) {
        isValid = false
        sections[currentSection].classList.remove("active")
        sections[i].classList.add("active")
        currentSection = i
        updateProgress()
        break
      }
    }

    return isValid
  }

  // Show error message
  function showError(input, message) {
    const formGroup = input.closest(".mb-3")
    let errorElement = formGroup.querySelector(".error-message")

    input.classList.add("is-invalid")

    if (!errorElement) {
      errorElement = document.createElement("div")
      errorElement.className = "error-message invalid-feedback"
      formGroup.appendChild(errorElement)
    }

    errorElement.textContent = message
  }

  // Remove error message
  function removeError(input) {
    input.classList.remove("is-invalid")
    input.classList.add("is-valid")
  }

  // Email validation
  function validateEmail(email) {
    const re =
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return re.test(String(email).toLowerCase())
  }

  // Phone validation
  function validatePhone(phone) {
    const re = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/
    return re.test(String(phone))
  }

  // Initialize the form
  initForm()
})
