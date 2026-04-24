class Validator {
  constructor() {
    this.errors = {}; // Stores validation errors
    this.rules = {}; // Stores validation rules for each field
  }

  /**
   * Adds validation rules for a specific field.
   * @param {string} field - The name of the field.
   * @param {Array<Function>} rules - Array of validation rule functions.
   */
  addRules(field, rules) {
    this.rules[field] = rules;
  }

  /**
   * Validates the provided data against the defined rules.
   * @param {Object} data - The subset of form data to validate in { field: value } format.
   * @param {Object} [fullData={}] - The full form data, provided for cross-field validation.
   * @returns {boolean} - True if all fields in `data` are valid, false otherwise.
   */
  validate(data, fullData = {}) {
    this.errors = {}; // Clear errors before validation

    for (const field in data) {
      if (this.rules[field]) {
        const value = data[field];
        const fieldRules = this.rules[field];

        for (const rule of fieldRules) {
          const error = rule(value, fullData);

          if (error) {
            if (!this.errors[field]) {
              this.errors[field] = error;
            }
            break;
          }
        }
      }
    }

    return Object.keys(this.errors).length === 0;
  }

  /**
   * Retrieves validation errors.
   * @returns {Object} - Validation errors in { field: [errors] } format.
   */
  getErrors() {
    return this.errors;
  }

  /**
   * Built-in validation rules for common use cases.
   */
  static rules = {
    isNotEmpty: (value) =>
      !value ? getTranslatedText("validation.required") : null,
    /*isNotEmpty: (value) => (!value ? getTranslation('validation.required') : null),*/
    isEmail: (value) =>
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
        ? getTranslatedText("validation.email")
        : null,
    /*isEmail: (value) =>
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? getTranslation('validation.email') : null,*/
    minLength: (length) => (value) =>
      value && value.length < length
        ? getTranslatedText("validation.min_length", { length })
        : null,
    /*minLength: (length) => (value) =>
            value && value.length < length ? getTranslation('validation.min_length', { length }) : null,*/
    maxLength: (length) => (value) =>
      value && value.length > length
        ? getTranslatedText("validation.max_length", { length })
        : null,
    /*maxLength: (length) => (value) =>
            value && value.length > length ? getTranslation('validation.max_length', { length }) : null,*/
    isPhoneNumber: (value) => {
      const trimmed = value.trim();

      const cleaned = trimmed.replace(/[_\s]/g, "");

      const validPattern = /^[\d()+-]+$/;
      if (!validPattern.test(cleaned)) {
        return getTranslatedText("validation.phone");
        /*return getTranslation('validation.phone');*/
      }

      if (cleaned.includes("_")) {
        return getTranslatedText("validation.phone");
        /*return getTranslation('validation.phone');*/
      }

      return null;
    },
    isAlpha: (value) =>
      !/^\p{L}+(?:[ -]\p{L}+)*$/u.test(value)
        ? getTranslatedText("validation.alpha")
        : null,
    /*isAlpha: (value) =>
            !/^\p{L}+(?:[ -]\p{L}+)*$/u.test(value) ? getTranslation('validation.alpha') : null,*/
    isOptionalAlpha: (value) => {
      if (!value || value.trim() === "") return null;
      return !/^\p{L}+(?:[ -]\p{L}+)*$/u.test(value)
        ? getTranslatedText("validation.alpha")
        : null;
    },
    /*isOptionalAlpha: (value) => {
            if (!value || value.trim() === '') return null;
            return !/^\p{L}+(?:[ -]\p{L}+)*$/u.test(value)
                ? getTranslation('validation.alpha')
                : null;
        },*/
    isNumber: (value) =>
      !/^\d+$/.test(value) ? getTranslatedText("validation.number") : null,
    /*isNumber: (value) =>
            !/^\d+$/.test(value) ? getTranslation('validation.number') : null,*/
    isPrice: (value) => {
      const normalized = value.replace(",", ".");
      return !/^\d+(\.\d{1,2})?$/.test(normalized) ||
        parseFloat(normalized) <= 0
        ? getTranslatedText("validation.price")
        : null;
    },
    /*isPrice: (value) => {
            const normalized = value.replace(',', '.');
            return !/^\d+(\.\d{1,2})?$/.test(normalized) || parseFloat(normalized) <= 0
                ? getTranslation('validation.price')
                : null;
        },*/
    isStrongPassword: (value) => {
      const hasUpperCase = /\p{Lu}/u.test(value);
      const hasLowerCase = /\p{Ll}/u.test(value);
      const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value);

      if (!hasUpperCase) {
        return getTranslatedText("validation.strong_password.uppercase");
      }
      if (!hasLowerCase) {
        return getTranslatedText("validation.strong_password.lowercase");
      }
      if (!hasSpecialChar) {
        return getTranslatedText("validation.strong_password.special");
      }

      return null;
    },
    /*isStrongPassword: (value) => {
            const hasUpperCase = /\p{Lu}/u.test(value);
            const hasLowerCase = /\p{Ll}/u.test(value);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value);

            if (!hasUpperCase) {
                return getTranslation('validation.strong_password.uppercase');
            }
            if (!hasLowerCase) {
                return getTranslation('validation.strong_password.lowercase');
            }
            if (!hasSpecialChar) {
                return getTranslation('validation.strong_password.special');
            }

            return null;
        },*/
    matchesField: (fieldToMatch) => (value, data) =>
      value !== data[fieldToMatch]
        ? getTranslatedText("validation.password_match")
        : null,
    /*matchesField: (fieldToMatch) => (value, data) =>
            value !== data[fieldToMatch]
                ? getTranslation('validation.password_match')
                : null,*/
  };
}

const validator = new Validator();

validator.addRules("name", [
  Validator.rules.isNotEmpty,
  Validator.rules.isAlpha,
]);
/*validator.addRules('surname', [Validator.rules.isNotEmpty, Validator.rules.isAlpha]);*/
validator.addRules("city", [
  Validator.rules.isNotEmpty,
  Validator.rules.isAlpha,
]);
validator.addRules("email", [
  Validator.rules.isNotEmpty,
  Validator.rules.isEmail,
]);
/*validator.addRules('phone', [Validator.rules.isNotEmpty, Validator.rules.isPhoneNumber]);*/
validator.addRules("profession", [Validator.rules.isOptionalAlpha]);
validator.addRules("password", [
  Validator.rules.isNotEmpty,
  Validator.rules.minLength(8),
  Validator.rules.isStrongPassword,
]);
validator.addRules("confirmPassword", [
  Validator.rules.isNotEmpty,
  Validator.rules.matchesField("password"),
]);
validator.addRules("oldPassword", [
  Validator.rules.isNotEmpty,
  Validator.rules.minLength(8),
  Validator.rules.isStrongPassword,
]);

const NOT_VALID_CLASS = "notValid";

function updateFieldValidationState(input, isValid, errorMessage = "") {
  const inputWrapper = input.closest(
    ".input__form, .input__formAd, .input__formTextarea",
  );

  if (!inputWrapper) {
    return;
  }

  const errorElement = inputWrapper.querySelector(".error");

  if (isValid) {
    inputWrapper.classList.remove(NOT_VALID_CLASS);
    errorElement.textContent = "";
  } else {
    inputWrapper.classList.add(NOT_VALID_CLASS);
    errorElement.textContent = errorMessage;
  }
}

function initializeFormValidation(form, formData, inputs) {
  inputs.forEach((input) => {
    input.addEventListener("input", (event) => {
      const fieldName = event.target.name;
      formData[fieldName] = event.target.value.trim();

      let fieldsToValidate = { [fieldName]: formData[fieldName] };

      if (
        fieldName === "confirmPassword" ||
        (fieldName === "password" && formData.hasOwnProperty("confirmPassword"))
      ) {
        fieldsToValidate = {
          password: formData["password"] || "",
          confirmPassword: formData["confirmPassword"] || "",
        };
      }

      const validationResults = {};
      for (const field in fieldsToValidate) {
        const isValidField = validator.validate(
          { [field]: fieldsToValidate[field] },
          formData,
        );
        const errors = validator.getErrors();
        validationResults[field] = {
          isValid: isValidField,
          error: errors[field] || "",
        };
      }

      for (const field in fieldsToValidate) {
        const inputField = form.querySelector(`[name="${field}"]`);
        if (inputField) {
          const { isValid, error } = validationResults[field];
          updateFieldValidationState(inputField, isValid, error);
        }
      }
    });
  });
}

function displayValidationErrors(errors, inputs = []) {
  if (!errors || typeof errors !== "object") {
    return;
  }

  const errorCount = Object.keys(errors).length;

  if (inputs.length > 0) {
    inputs.forEach((input) => {
      const fieldName = input.name;
      const errorMessage = errors[fieldName] || "";
      const isValid = !errorMessage;
      updateFieldValidationState(input, isValid, errorMessage);
    });
  } else {
    for (let field in errors) {
      if (errors.hasOwnProperty(field)) {
        const errorMessage = errors[field];
        const input = document.querySelector(`[name="${field}"]`);
        updateFieldValidationState(input, false, errorMessage);
      }
    }
  }

  if (errorCount > 1) {
    MessageSystem.showMessage(
      "warning",
      getTranslatedText("validation_failed"),
    );
    /*MessageSystem.showMessage('warning', getTranslation('validation_failed'));*/
  } else if (errorCount === 1) {
    const errorMessage = Object.values(errors)[0];
    MessageSystem.showMessage("warning", errorMessage);
  }
}

// Register
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formRegister");

  if (!form) return;

  const formContainer = form.closest(".actionTemplate__innerForm");

  const formData = {};
  const inputs = form.querySelectorAll("input");

  inputs.forEach((input) => {
    formData[input.name] = input.value.trim();
  });

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    inputs.forEach((input) => {
      formData[input.name] = input.value.trim();
    });

    if (!validator.validate(formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
    } else {
      // Перевірка наявності токену Turnstile
      const turnstileToken = document.getElementById("turnstile-token");
      if (!turnstileToken || !turnstileToken.value) {
        MessageSystem.showMessage(
          "warning",
          "Будь ласка, підтвердіть, що ви не робот.",
        );
        return;
      }

      toggleLoadingCursor(true);
      formContainer.classList.add("disabled");

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "register_form",
          security: securityObject.register_nonce,
          formData: JSON.stringify(formData),
          turnstile_token: turnstileToken.value,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            //console.log('Successful answer:', data);
            form.reset();
            formContainer.classList.add("hidden");
            document.querySelector("#checkEmail").classList.remove("hidden");
            MessageSystem.showMessage(
              "success",
              getTranslatedText("registration_success"),
            );
            /*MessageSystem.showMessage('success', getTranslation('registration_success'));*/
          } else {
            //console.error('Errors:', data.data.errors);
            displayValidationErrors(data.data.errors);

            // Скидаємо Turnstile при помилці
            if (typeof turnstile !== "undefined") {
              const turnstileWidget = document.querySelector(".cf-turnstile");
              if (turnstileWidget) {
                turnstile.reset(turnstileWidget);
              }
            }
            document.getElementById("turnstile-token").value = "";
          }
        })
        .catch((error) => {
          //console.error('Request error:', error.message);

          // Скидаємо Turnstile при помилці запиту
          if (typeof turnstile !== "undefined") {
            const turnstileWidget = document.querySelector(".cf-turnstile");
            if (turnstileWidget) {
              turnstile.reset(turnstileWidget);
            }
          }
          document.getElementById("turnstile-token").value = "";
        })
        .finally(() => {
          toggleLoadingCursor(false);
          formContainer.classList.remove("disabled");
        });
    }
  });

  const resendButton = document.querySelector(
    ".actionTemplate__resend span#resendEmail",
  );
  const resendButtonText = resendButton.innerText;

  if (resendButton) {
    resendButton.addEventListener("click", function () {
      timer(resendButton, resendButtonText);

      const email = formData.email;

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "resend_confirmation_email",
          security: securityObject.resend_nonce,
          email: email,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            MessageSystem.showMessage(
              "info",
              getTranslatedText("email_resent"),
            );
            /*MessageSystem.showMessage('info', getTranslation('email_resent'));*/
            //console.log('The email was successfully resent.');
          } else {
            //console.log('Error resending the email. Please try again.');
          }
        })
        .catch((error) => {
          //console.error('Request error:', error.message);
        });
    });
  }
});

// Login
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formLogin");

  if (!form) return;

  const formContainer = form.closest(".actionTemplate__innerForm");

  const formData = {};
  const inputs = form.querySelectorAll("input");

  inputs.forEach((input) => {
    formData[input.name] = input.value.trim();
  });

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!validator.validate(formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
    } else {
      toggleLoadingCursor(true);
      formContainer.classList.add("disabled");

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "login_user",
          security: securityObject.login_nonce,
          formData: JSON.stringify(formData),
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            //console.log('Login successful:', data);
            setMessageCookies(
              "success",
              getTranslatedText("login_success"),
              60,
            );
            /*setMessageCookies('success', getTranslation('login_success'), 60);*/
            window.location.href = data.data.redirect_url || "/";
          } else {
            //console.error('Login errors:', data.data.errors);
            displayValidationErrors(data.data.errors);
          }
        })
        .catch((error) => {
          //console.error('Login request error:', error.message);
        })
        .finally(() => {
          toggleLoadingCursor(false);
          formContainer.classList.remove("disabled");
        });
    }
  });

  // Social Login handlers
  const googleLoginBtn = document.querySelector("#btnGoogleLogin");
  const facebookLoginBtn = document.querySelector("#btnFacebookLogin");

  if (googleLoginBtn) {
    googleLoginBtn.addEventListener("click", function (e) {
      e.preventDefault();
      handleSocialLogin("google");
    });
  }

  if (facebookLoginBtn) {
    facebookLoginBtn.addEventListener("click", function (e) {
      e.preventDefault();
      handleSocialLogin("facebook");
    });
  }
});

/**
 * Handle social login
 * @param {string} provider - 'google' or 'facebook'
 */
function handleSocialLogin(provider) {
  // Option 1: Use Nextend Social Login plugin
  // If plugin is installed, it will handle the redirect automatically
  if (typeof nsl !== "undefined" && nsl[provider]) {
    // Add redirect parameter to ensure user is redirected to home page instead of /wp-admin
    const loginUrl = new URL(nsl[provider].url, window.location.origin);
    loginUrl.searchParams.set("redirect", window.location.origin);
    window.location.href = loginUrl.toString();
    return;
  }

  // Option 2: Custom implementation
  // Redirect to custom OAuth endpoint
  fetch(mainObject.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action: "social_login_init",
      provider: provider,
      security: securityObject.login_nonce,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.auth_url) {
        window.location.href = data.data.auth_url;
      } else {
        console.error(
          "Social login error:",
          data.data?.message || "Unknown error",
        );
        alert(data.data?.message || "Помилка входу через соціальну мережу");
      }
    })
    .catch((error) => {
      console.error("Social login request error:", error);
      alert("Помилка підключення до сервера");
    });
}

//Forgot Password
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formForgot");

  if (!form) return;

  const formContainer = form.closest(".actionTemplate__innerPopUp");

  const formData = {};
  const inputs = form.querySelectorAll("input");

  inputs.forEach((input) => {
    formData[input.name] = input.value.trim();
  });

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!validator.validate(formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
    } else {
      toggleLoadingCursor(true);
      formContainer.classList.add("disabled");

      const email = formData.email;

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "forgot_pass",
          security: securityObject.forgot_nonce,
          email: email,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            //console.log('Login successful:', data);
            form.reset();
            formContainer.classList.add("hidden");
            MessageSystem.showMessage(
              "success",
              getTranslatedText("password_change_request_success"),
            );
            /*MessageSystem.showMessage('success', getTranslation('password_change_request_success'));*/
            document.querySelector("#checkEmail").classList.remove("hidden");
          } else {
            //console.error('Login errors:', data.data.errors);
            displayValidationErrors(data.data.errors);
          }
        })
        .catch((error) => {
          //console.error('Login request error:', error.message);
        })
        .finally(() => {
          toggleLoadingCursor(false);
          formContainer.classList.remove("disabled");
        });
    }
  });

  const resendButton = document.querySelector(
    ".actionTemplate__resend span#resendEmail",
  );
  const resendButtonText = resendButton.innerText;

  if (resendButton) {
    resendButton.addEventListener("click", function () {
      timer(resendButton, resendButtonText);

      const email = formData.email;

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "forgot_pass",
          security: securityObject.forgot_nonce,
          email: email,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            MessageSystem.showMessage(
              "info",
              getTranslatedText("email_resent"),
            );
            /*MessageSystem.showMessage('info', getTranslation('email_resent'));*/
            //console.log('The email was successfully resent.');
          } else {
            //console.log('Error resending the email. Please try again.');
          }
        })
        .catch((error) => {
          //console.error('Request error:', error.message);
        });
    });
  }
});

//Reset Password
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formReset");

  if (!form) return;

  const formContainer = form.closest(".actionTemplate__innerPopUp");

  const formData = {};
  const inputs = form.querySelectorAll("input");

  inputs.forEach((input) => {
    formData[input.name] = input.value.trim();
  });

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!validator.validate(formData, formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
    } else {
      toggleLoadingCursor(true);
      formContainer.classList.add("disabled");

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "reset_password",
          security: securityObject.reset_nonce,
          formData: JSON.stringify(formData),
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            //console.log('Password successfully reset.:', data);
            setMessageCookies(
              "success",
              getTranslatedText("password_reset_success"),
              60,
            );
            /*setMessageCookies('success', getTranslation('password_reset_success'), 60);*/
            window.location.href = data.redirect_url || "/";
          } else {
            //console.error('Password reset errors:', data.data.errors);
            displayValidationErrors(data.data.errors);
          }
        })
        .catch((error) => {
          //console.error('Password reset request error:', error.message);
        })
        .finally(() => {
          toggleLoadingCursor(false);
          formContainer.classList.remove("disabled");
        });
    }
  });
});

//Edit Profile
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formEditProfile");

  if (!form) return;

  const editButton = document.querySelector("#editProfile");
  const cancelButton = document.querySelector("#editCancelProfile");
  const showDataProfile = document.querySelector("#showDataProfile");

  const formData = {};
  const initialValidationState = {};
  const inputs = form.querySelectorAll("input:not(.noEdit input)");

  inputs.forEach((input) => {
    formData[input.name] = input.value.trim();
    initialValidationState[input.name] = input.value.trim();
  });

  editButton.addEventListener("click", () => {
    showDataProfile.classList.add("hidden");
    form.classList.remove("hidden");
  });

  cancelButton.addEventListener("click", () => {
    form.classList.add("hidden");
    showDataProfile.classList.remove("hidden");

    Object.assign(formData, initialValidationState);

    inputs.forEach((input) => {
      if (input.name && initialValidationState[input.name] !== undefined) {
        input.value = initialValidationState[input.name];
        input.dispatchEvent(new Event("input"));
      }
    });
  });

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    inputs.forEach((input) => {
      formData[input.name] = input.value.trim();
    });

    if (!validator.validate(formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
    } else {
      toggleLoadingCursor(true);
      form.classList.add("disabled");

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "edit_profile",
          security: securityObject.edit_nonce,
          formData: JSON.stringify(formData),
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            //console.log('Successful answer:', data);
            setMessageCookies(
              "success",
              getTranslatedText("main_info_updated_success"),
              60,
            );
            /*setMessageCookies('success', getTranslation('main_info_updated_success'), 60);*/
            window.location.reload();
          } else {
            //console.error('Errors:', data.data.errors);
            displayValidationErrors(data.data.errors);
          }
        })
        .catch((error) => {
          //console.error('Request error:', error.message);
        })
        .finally(() => {
          toggleLoadingCursor(false);
          form.classList.remove("disabled");
        });
    }
  });

  const verifiedButton = document.querySelector("#emailVerified");

  if (verifiedButton) {
    const verifiedButtonText = verifiedButton.innerText;

    verifiedButton.addEventListener("click", function () {
      timer(verifiedButton, verifiedButtonText);

      const emailInput = form.querySelector("#email");
      const email = emailInput?.value;

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "resend_confirmation_email",
          security: securityObject.resend_nonce,
          email: email,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            MessageSystem.showMessage(
              "info",
              getTranslatedText("email_resent"),
            );
            /*MessageSystem.showMessage('info', getTranslation('email_resent'));*/
            //console.log('The email was successfully resent.');
          } else {
            //console.log('Error resending the email. Please try again.');
          }
        })
        .catch((error) => {
          //console.error('Request error:', error.message);
        });
    });
  }
});

//Change Password
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formEditPass");

  if (!form) return;

  const formData = {};
  const inputs = form.querySelectorAll("input");

  inputs.forEach((input) => {
    formData[input.name] = input.value.trim();
  });

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!validator.validate(formData, formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
    } else {
      toggleLoadingCursor(true);
      form.classList.add("disabled");

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "change_password",
          security: securityObject.change_nonce,
          formData: JSON.stringify(formData),
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            //console.log('Password successfully reset.:', data);
            form.reset();
            MessageSystem.showMessage(
              "success",
              getTranslatedText("password_updated_successfully"),
            );
            /*MessageSystem.showMessage('success', getTranslation('password_updated_successfully'));*/
          } else {
            //console.error('Password reset errors:', data.data.errors);
            displayValidationErrors(data.data.errors);
          }
        })
        .catch((error) => {
          //console.error('Password reset request error:', error.message);
        })
        .finally(() => {
          toggleLoadingCursor(false);
          form.classList.remove("disabled");
        });
    }
  });
});

validator.addRules("adName", [
  Validator.rules.isNotEmpty,
  Validator.rules.maxLength(100),
]);
validator.addRules("adCategory", [Validator.rules.isNotEmpty]);
validator.addRules("adDesc", [
  Validator.rules.isNotEmpty,
  Validator.rules.maxLength(2000),
]);
validator.addRules("adType", [Validator.rules.isNotEmpty]);
/*validator.addRules('adPrice', [Validator.rules.isNotEmpty, Validator.rules.isPrice]);*/
validator.addRules("adCurrency", [Validator.rules.isNotEmpty]);
validator.addRules("adCondition", [Validator.rules.isNotEmpty]);

//Create Ad
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formAdCreate");

  if (!form) return;

  const stepInfo = document.getElementById("stepInfo");
  const stepType = document.getElementById("stepType");
  const stepPayment = document.getElementById("stepPayment");
  const stepSuccess = document.getElementById("stepSuccess");
  const adCreateButton = document.getElementById("adCreateButton");

  const formData = {};
  const inputs = form.querySelectorAll("input, textarea");

  function gatherFormData() {
    inputs.forEach((input) => {
      if (input.type === "file") return;
      if (input.type === "radio") {
        if (input.checked) {
          formData[input.name] = input.value.trim();
        }
      } else {
        formData[input.name] = input.value.trim();
      }
    });

    const adCategoryInput = form.querySelector("#adCategory");
    if (adCategoryInput) {
      const selectedCategoryId =
        adCategoryInput.dataset.subcategoryId ||
        adCategoryInput.dataset.categoryId ||
        "";
      if (selectedCategoryId) {
        formData.adCategoryId = selectedCategoryId;
      } else {
        delete formData.adCategoryId;
      }
    }
  }

  const previewContainer = document.getElementById("previewContainer");
  const maxFiles = 10;
  let filesArray = [];

  function renderPreview() {
    previewContainer.innerHTML = "";

    filesArray.forEach((file, index) => {
      const item = document.createElement("div");
      item.classList.add("preview-item", "filled");
      item.innerHTML = `
                <img src="${file.preview}" alt="${file.name}" />
                <div class="remove-btn" data-index="${index}"></div>
                ${
                  index === 0
                    ? `<div class="main-label body2">${getTranslatedText(
                        "main_label",
                      )}</div>`
                    : ""
                }
            `;
      /*item.innerHTML = `
                <img src="${file.preview}" alt="${file.name}" />
                <div class="remove-btn" data-index="${index}"></div>
                ${index === 0 ? `<div class="main-label body2">${getTranslation('main_label')}</div>` : ""}
            `;*/

      item.querySelector(".remove-btn").addEventListener("click", () => {
        filesArray.splice(index, 1);
        renderPreview();
      });

      previewContainer.appendChild(item);
    });

    if (filesArray.length < maxFiles) {
      const addPhotoItem = document.createElement("div");
      addPhotoItem.classList.add("preview-item", "add-photo");
      addPhotoItem.innerHTML = `
                <input type="file" accept="image/jpeg, image/png" />
                <span class="overline">${getTranslatedText("add_photo")}</span>
            `;
      /*addPhotoItem.innerHTML = `
                <input type="file" accept="image/jpeg, image/png" />
                <span class="overline">${getTranslation('add_photo')}</span>
            `;*/

      addPhotoItem.addEventListener("click", () => {
        const input = addPhotoItem.querySelector("input[type='file']");
        input.click();
      });

      addPhotoItem
        .querySelector("input")
        .addEventListener("change", handleFileInput);
      previewContainer.appendChild(addPhotoItem);
    }

    for (let i = filesArray.length + 1; i < maxFiles; i++) {
      const placeholderItem = document.createElement("div");
      placeholderItem.classList.add("preview-item", "placeholder");
      previewContainer.appendChild(placeholderItem);
    }
  }

  function handleFileInput(event) {
    const file = event.target.files[0];

    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
      MessageSystem.showMessage(
        "warning",
        getTranslatedText("file_size_limit"),
      );
      /*MessageSystem.showMessage('warning', getTranslation('file_size_limit'));*/
      return;
    }

    const supportedFormats = ["image/jpeg", "image/png"];
    if (!supportedFormats.includes(file.type)) {
      MessageSystem.showMessage(
        "warning",
        getTranslatedText("unsupported_file_format"),
      );
      /*MessageSystem.showMessage('warning', getTranslation('unsupported_file_format'));*/
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      filesArray.push({ name: file.name, preview: e.target.result, file });
      renderPreview();
    };
    reader.readAsDataURL(file);

    event.target.value = "";
  }

  renderPreview();

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    gatherFormData();

    if (!validator.validate(formData, formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
      return;
    }

    /*const minImages = 1;

        if (filesArray.length < minImages) {
            MessageSystem.showMessage('warning', getTranslation('at_least_one_photo'));
            return;
        }*/

    stepInfo.classList.add("hidden");
    stepType.classList.remove("hidden");
    window.scrollTo(0, 0);
  });

  adCreateButton.addEventListener("click", function () {
    const activeItem = document.querySelector(".js-adType.active");

    if (activeItem) {
      const adType = activeItem.getAttribute("data-adType");

      if (formData.adPrice) {
        formData.adPrice = formData.adPrice.replace(",", ".");
      }

      const formDataToSend = new FormData();
      formDataToSend.append("action", "create_ad");
      formDataToSend.append("security", securityObject.adCreate_nonce);
      formDataToSend.append("formData", JSON.stringify(formData));
      filesArray.forEach((fileObj, index) => {
        formDataToSend.append(`files[${index}]`, fileObj.file);
      });

      toggleLoadingCursor(true);

      fetch(mainObject.ajax_url, {
        method: "POST",
        body: formDataToSend,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const postId = data.data.postId;
            const formBoost = document.getElementById("formBoost");

            if (formBoost) {
              formBoost.dataset.postId = postId;
            }

            form.reset();
            stepType.classList.add("hidden");
            if (adType === "boost") {
              stepPayment.classList.remove("hidden");
              window.scrollTo(0, 0);
            }
            if (adType === "free") {
              stepSuccess.classList.remove("hidden");
              window.scrollTo(0, 0);
            }
          } else {
            if (data?.data?.errors && typeof data.data.errors === "object") {
              displayValidationErrors(data.data.errors);
            } else {
              MessageSystem.showMessage(
                "error",
                data?.data?.message || getTranslatedText("error_generic")
              );
            }
          }
        })
        .catch((error) => {
          MessageSystem.showMessage("error", getTranslatedText("server_error"));
          /*MessageSystem.showMessage('error', getTranslation('server_error'));*/
        })
        .finally(() => {
          toggleLoadingCursor(false);
        });
    }
  });

  const backToForm = document.querySelector(".js-backToForm");

  backToForm.addEventListener("click", function () {
    stepType.classList.add("hidden");
    stepInfo.classList.remove("hidden");
    window.scrollTo(0, 0);
  });

  const goSuccess = document.querySelector(".js-goSuccess");

  goSuccess.addEventListener("click", function () {
    stepPayment.classList.add("hidden");
    stepSuccess.classList.remove("hidden");
    window.scrollTo(0, 0);
  });
});

function toggleLoadingCursor(isLoading) {
  if (isLoading) {
    document.body.classList.add("loading");
  } else {
    document.body.classList.remove("loading");
  }
}

function timer(resendButton, resendButtonText) {
  let countdown = 60;
  resendButton.innerText = getTranslatedText("resend_button_wait", {
    countdown,
  });
  /*resendButton.innerText = getTranslation('resend_button_wait', { countdown });*/
  resendButton.style.pointerEvents = "none";

  const interval = setInterval(() => {
    countdown--;
    resendButton.innerText = getTranslatedText("resend_button_wait", {
      countdown,
    });
    /*resendButton.innerText = getTranslation('resend_button_wait', { countdown });*/

    if (countdown <= 0) {
      clearInterval(interval);
      resendButton.innerText = resendButtonText;
      resendButton.style.pointerEvents = "auto";
    }
  }, 1000);
}

//Edit Ad
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formAdEdit");

  if (!form) return;

  const postId = form.dataset.postId;

  const formData = {};
  const inputs = form.querySelectorAll("input, textarea");

  function gatherFormData() {
    inputs.forEach((input) => {
      if (input.type === "file") return;
      if (input.type === "radio") {
        if (input.checked) {
          formData[input.name] = input.value.trim();
        }
      } else {
        let value = input.value.trim();
        if (input.type === "number" || input.dataset.numeric === "true") {
          value = value.replace(",", ".");
        }
        formData[input.name] = value;
      }
    });

    const adCategoryInput = form.querySelector("#adCategory");
    if (adCategoryInput) {
      const selectedCategoryId =
        adCategoryInput.dataset.subcategoryId ||
        adCategoryInput.dataset.categoryId ||
        "";
      if (selectedCategoryId) {
        formData.adCategoryId = selectedCategoryId;
      } else {
        delete formData.adCategoryId;
      }
    }
  }

  const previewContainer = document.getElementById("previewContainer");
  const maxFiles = 10;
  let filesArray = [
    ...(typeof initialImages !== "undefined" && Array.isArray(initialImages)
      ? initialImages
      : []),
  ];

  function renderPreview() {
    previewContainer.innerHTML = "";

    filesArray.forEach((file, index) => {
      const item = document.createElement("div");
      item.classList.add("preview-item", "filled");
      item.innerHTML = `
                <img src="${file.preview}" alt="${file.name}" />
                <div class="remove-btn" data-index="${index}"></div>
                ${
                  index === 0
                    ? `<div class="main-label body2">${getTranslatedText(
                        "main_label",
                      )}</div>`
                    : ""
                }
            `;
      /*item.innerHTML = `
                <img src="${file.preview}" alt="${file.name}" />
                <div class="remove-btn" data-index="${index}"></div>
                ${index === 0 ? `<div class="main-label body2">${getTranslation('main_label')}</div>` : ""}
            `;*/

      item.querySelector(".remove-btn").addEventListener("click", () => {
        filesArray.splice(index, 1);
        renderPreview();
      });

      previewContainer.appendChild(item);
    });

    if (filesArray.length < maxFiles) {
      const addPhotoItem = document.createElement("div");
      addPhotoItem.classList.add("preview-item", "add-photo");
      addPhotoItem.innerHTML = `
                <input type="file" accept="image/jpeg, image/png" />
                <span class="overline">${getTranslatedText("add_photo")}</span>
            `;
      /*addPhotoItem.innerHTML = `
                <input type="file" accept="image/jpeg, image/png" />
                <span class="overline">${getTranslation('add_photo')}</span>
            `;*/

      addPhotoItem.addEventListener("click", () => {
        const input = addPhotoItem.querySelector("input[type='file']");
        input.click();
      });

      addPhotoItem
        .querySelector("input")
        .addEventListener("change", handleFileInput);
      previewContainer.appendChild(addPhotoItem);
    }

    for (let i = filesArray.length + 1; i < maxFiles; i++) {
      const placeholderItem = document.createElement("div");
      placeholderItem.classList.add("preview-item", "placeholder");
      previewContainer.appendChild(placeholderItem);
    }
  }

  function handleFileInput(event) {
    const file = event.target.files[0];

    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
      MessageSystem.showMessage(
        "warning",
        getTranslatedText("file_size_limit"),
      );
      /*MessageSystem.showMessage('warning', getTranslation('file_size_limit'));*/
      return;
    }

    const supportedFormats = ["image/jpeg", "image/png"];
    if (!supportedFormats.includes(file.type)) {
      MessageSystem.showMessage(
        "warning",
        getTranslatedText("unsupported_file_format"),
      );
      /*MessageSystem.showMessage('warning', getTranslation('unsupported_file_format'));*/
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      filesArray.push({ name: file.name, preview: e.target.result, file });
      renderPreview();
    };
    reader.readAsDataURL(file);

    event.target.value = "";
  }

  renderPreview();

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    gatherFormData();

    if (!validator.validate(formData, formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
      return;
    }

    /*const minImages = 1;

        if (filesArray.length < minImages) {
            MessageSystem.showMessage('warning', getTranslation('at_least_one_photo'));
            return;
        }*/

    if (postId) {
      const formDataToSend = new FormData();
      formDataToSend.append("action", "edit_ad");
      formDataToSend.append("security", securityObject.adEdit_nonce);
      formDataToSend.append("postId", postId);
      formDataToSend.append("formData", JSON.stringify(formData));

      const existingImages = [];
      const newImages = [];

      filesArray.forEach((fileObj) => {
        if (fileObj.existing) {
          existingImages.push(fileObj.preview);
        } else {
          newImages.push(fileObj.file);
        }
      });

      existingImages.forEach((url) => {
        formDataToSend.append("existingImages[]", url);
      });

      newImages.forEach((file) => {
        formDataToSend.append("newImages[]", file);
      });

      toggleLoadingCursor(true);

      fetch(mainObject.ajax_url, {
        method: "POST",
        body: formDataToSend,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            setMessageCookies(
              "success",
              getTranslatedText("announcement_edited"),
              60,
            );
            /*setMessageCookies('success', getTranslation('announcement_edited'), 60);*/
            window.location.href = data.data.postUrl;
          } else {
            if (data?.data?.errors && typeof data.data.errors === "object") {
              displayValidationErrors(data.data.errors);
            } else {
              MessageSystem.showMessage(
                "error",
                data?.data?.message || getTranslatedText("error_generic")
              );
            }
          }
        })
        .catch((error) => {
          MessageSystem.showMessage("error", getTranslatedText("server_error"));
          /*MessageSystem.showMessage('error', getTranslation('server_error'));*/
        })
        .finally(() => {
          toggleLoadingCursor(false);
        });
    }
  });
});

/*validator.addRules("contactName", [
  Validator.rules.isNotEmpty,
  Validator.rules.isAlpha,
]);
validator.addRules("contactSurname", [
  Validator.rules.isNotEmpty,
  Validator.rules.isAlpha,
]);
validator.addRules("contactEmail", [
  Validator.rules.isNotEmpty,
  Validator.rules.isEmail,
]);*/
/*validator.addRules('contactPhone', [Validator.rules.isNotEmpty, Validator.rules.isPhoneNumber]);*/
/*validator.addRules("contactMessage", [
  Validator.rules.isNotEmpty,
  Validator.rules.maxLength(500),
]);*/

validator.addRules("contactCompany", [
  Validator.rules.isNotEmpty,
  Validator.rules.isAlpha,
]);
validator.addRules("contactEmail", [
  Validator.rules.isNotEmpty,
  Validator.rules.isEmail,
]);

//Contact Us
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#formContact");

  if (!form) return;

  const formData = {};
  const inputs = form.querySelectorAll("input, textarea");

  inputs.forEach((input) => {
    formData[input.name] = input.value.trim();
  });

  initializeFormValidation(form, formData, inputs);

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const contactPhoneInput = form.querySelector('input[name="contactPhone"]');
    if (contactPhoneInput) {
      contactPhoneInput.dispatchEvent(new Event("input"));
    }

    if (!validator.validate(formData)) {
      displayValidationErrors(validator.getErrors(), inputs);
    } else {
      toggleLoadingCursor(true);

      fetch(mainObject.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "contact_form",
          security: securityObject.contact_nonce,
          formData: JSON.stringify(formData),
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            //console.log('Successful answer:', data);
            form.reset();
            MessageSystem.showMessage(
              "success",
              getTranslatedText("email_sent"),
            );
            /*MessageSystem.showMessage('success', getTranslation('email_sent'));*/
          } else {
            //console.error('Errors:', data.data.errors);
            displayValidationErrors(data.data.errors);
          }
        })
        .catch((error) => {
          //console.error('Request error:', error.message);
        })
        .finally(() => {
          toggleLoadingCursor(false);
        });
    }
  });
});
