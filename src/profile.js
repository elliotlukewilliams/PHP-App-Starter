/**
 * Account page functionality: profile edit and account deletion
 */

/**
 * GLOBAL SCOPE
 */
// DOM elements
const PROFILE_UPDATE_FORM_ELEMENT = document.getElementById('profile-edit-form')
const DELETE_ACCOUNT_FORM_ELEMENT = document.getElementById('delete-account-form')
const PROFILE_IMAGE_EDIT_TOGGLE = document.getElementById('profile-image-edit-panel-toggle')
const PROFILE_IMAGE_EDIT_INPUT_FIELD = document.getElementById('profile-edit-image')
const REMOVE_PROFILE_IMAGE_BUTTON = document.getElementById('remove-profile-image')

// Reset state for form data
let OLD_PROFILE_UPDATE_FORM_DATA = 
    (PROFILE_UPDATE_FORM_ELEMENT && PROFILE_UPDATE_FORM_ELEMENT instanceof HTMLFormElement) ?
    new FormData(PROFILE_UPDATE_FORM_ELEMENT) :
    null

// Track if profile image has been updated
let PROFILE_IMAGE_UPDATED = false

// Fallback error message for unexpected failures
const GENERIC_ERROR_MESSAGE = 'Sorry, something went wrong. Please try again later'

// Open or close the profile image edit panel when clicking on the profile image trigger
const toggleProfileImageEditPanel = () => {
    if (!PROFILE_IMAGE_EDIT_TOGGLE || !PROFILE_IMAGE_EDIT_TOGGLE instanceof HTMLElement) {
        return
    }
    const editPanel = document.getElementById('profile-image-edit-panel')
    if (!editPanel) {
        return;
    }
    const isOpen = PROFILE_IMAGE_EDIT_TOGGLE.classList.contains('is-open')
    const triggerActiveClasses = ['-translate-x-1/4', 'is-open']
    if (!isOpen) {
        PROFILE_IMAGE_EDIT_TOGGLE.classList.add(...triggerActiveClasses)
        editPanel.classList.remove('hidden')
    } else {
        PROFILE_IMAGE_EDIT_TOGGLE.classList.remove(...triggerActiveClasses)
        editPanel.classList.add('hidden')
    }
    PROFILE_IMAGE_EDIT_TOGGLE.setAttribute('aria-expanded', String(!isOpen))
}

const toggleLoadingState = (loaderId = 'profile-edit-loader') => {
    const loader = document.getElementById(loaderId)
    if (loader) {
        loader.classList.toggle('hidden')

        if (loader.classList.contains('hidden')) {
            loader.ariaHidden = 'true'
        } else {
            loader.ariaHidden = 'false'
        }
    }
}

const showErrorMessage = (errorMessage, formElement = PROFILE_UPDATE_FORM_ELEMENT) => {
    const errorOutput = formElement?.querySelector('output')
    if (!errorOutput) return

    errorOutput.classList.add('active')
    errorOutput.innerHTML = errorMessage
}

const clearErrors = (formElement = PROFILE_UPDATE_FORM_ELEMENT) => {
    formElement?.querySelectorAll("output.active").forEach(error => {
        error.classList.remove("active")
        error.innerHTML = ''
    })
}

// Preview profile image
const displayPreviewProfileImageUpdate = () => {
    clearErrors()
    const profilePictureContainer = document.getElementById('profile-picture')
    if (!profilePictureContainer) return

    const existingPreviewImage = profilePictureContainer.querySelector('.profile-image-preview')
    if (existingPreviewImage) {
        existingPreviewImage.classList.add('hidden')
    }

    const file = PROFILE_IMAGE_EDIT_INPUT_FIELD.files?.[0]
    if (!file) return

    // Check file size. If too large, reset field value and show error
    if (file.size > 5000000) {
        showErrorMessage('File size too large. Profile images must be no larger than 5MB')
        PROFILE_IMAGE_EDIT_INPUT_FIELD.value = ''
        return
    }

    PROFILE_IMAGE_UPDATED = true

    // Check/create image preview in DOM and apply source from reader
    const reader = new FileReader()
    reader.onload = () => {
        let image = profilePictureContainer.querySelector('img.new-profile-image-preview')
        if (!image) {
            image = document.createElement('img')
            image.alt = 'Your new profile picture'
            image.classList.add(
                'new-profile-image-preview',
                'size-full',
                'absolute',
                'inset-0',
                'object-cover',
                'object-fit'
            )
        }
        image.src = reader.result
        profilePictureContainer.appendChild(image)
    }
    reader.onerror = () => {
        console.error("FileReader failed")
    }
    reader.readAsDataURL(file)

    // Close profile image edit panel
    toggleProfileImageEditPanel()
}

// Remove profile image
const removeProfileImage = () => {
    const imageFileInputElement = document.getElementById('profile-edit-image')
    const newProfileImagePreview = document.querySelector('.new-profile-image-preview')
    const originalProfileImage = document.querySelector('.profile-image-preview')

    // Remove new image preview and restore old one or remove original for option to have no profile image
    if (newProfileImagePreview) {
        newProfileImagePreview.remove()
        originalProfileImage?.classList.remove('hidden')
        if (imageFileInputElement) {
            imageFileInputElement.value = ''
        }
        PROFILE_IMAGE_UPDATED = false
    } else if (originalProfileImage && imageFileInputElement) {
        originalProfileImage.classList.add('hidden')
        imageFileInputElement.value = ''
        PROFILE_IMAGE_UPDATED = true
    }
    
    // Close profile image edit panel
    toggleProfileImageEditPanel()
}

// Reset form and element states
const resetProfileEditForm = (event) => {
    // Close profile image edit panel when modal is closed
    const modalParentElement = PROFILE_IMAGE_EDIT_TOGGLE.closest('.modal')
    if (!modalParentElement) {
        return
    } 
    const eventModalId = event.detail?.modalId
    const modalParentElementId = modalParentElement?.id
    if (
        (eventModalId && modalParentElementId) && 
        (eventModalId === modalParentElementId) &&
        PROFILE_IMAGE_EDIT_TOGGLE.classList.contains('is-open')
    ) {
        toggleProfileImageEditPanel() 
    }

    // Reset form fields
    const form = modalParentElement.querySelector('form')
    if (form) {
        form.reset()
    }

    // Revert profile image update
    const originalProfileImagePreview = modalParentElement.querySelector('.profile-image-preview')
    if (originalProfileImagePreview && originalProfileImagePreview.classList.contains('hidden')) {
        originalProfileImagePreview.classList.remove('hidden')
    }
    const newProfileImagePreview = modalParentElement.querySelector('.new-profile-image-preview')
    if (newProfileImagePreview) {
        newProfileImagePreview.remove()
    }
    PROFILE_IMAGE_UPDATED = false

    // Clear errors
    clearErrors()
}

// Submit profile edit
const submitProfileEdit = async () => {
    // Build a filtered copy of the form data containing only changed fields
    const submittedFormData = new FormData(PROFILE_UPDATE_FORM_ELEMENT)
    const filteredFormData = new FormData()
    try {
        toggleLoadingState();
        clearErrors()

        // Filter out data that hasn't changed to minimise payload. Only include the image file if updated
        for (const [fieldKey, fieldValue] of submittedFormData.entries()) {
            if (fieldKey === 'image_file') {
                if (PROFILE_IMAGE_UPDATED) {
                    filteredFormData.append(fieldKey, fieldValue)
                }
            } else if (fieldValue !== OLD_PROFILE_UPDATE_FORM_DATA.get(fieldKey)) {
                filteredFormData.append(fieldKey, fieldValue)
            }
        }

        // If no changed fields, show notice and exit
        const allUpdatedTextFields = Array.from(filteredFormData.keys()).filter((key) => key !== 'image_file')
        if (allUpdatedTextFields.length === 0 && !PROFILE_IMAGE_UPDATED) {
            showErrorMessage('No fields have been updated')
            return
        }
        
        const response = await fetch('/includes/endpoints/handle-user-edit.php', {
            method: 'POST',
            body: filteredFormData
        })

        const result = await response.json()

        if (result?.status !== 200) {
            const errorMessage = result?.data?.error_message
            if (errorMessage) {
                showErrorMessage(errorMessage)
            }
            return
        }

        // Set a one time local-storage item so we can show success message after page reload
        localStorage.setItem('profileUpdated', true)
        
        // Reload page
        window.location.reload()
    } catch (error) {
        console.error(error)
    } finally {
        toggleLoadingState();
    }
}

// Delete the current user's account, then redirect to the homepage
const submitDeleteAccount = async () => {
    const loaderId = 'delete-account-loader'
    let isRedirecting = false

    try {
        toggleLoadingState(loaderId)
        clearErrors(DELETE_ACCOUNT_FORM_ELEMENT)

        const response = await fetch('/includes/endpoints/handle-user-delete.php', {
            method: 'POST'
        })

        // A non-JSON response (e.g. a server error page) throws here and is handled below
        const result = await response.json()

        if (result?.status !== 200) {
            showErrorMessage(result?.data?.error_message || GENERIC_ERROR_MESSAGE, DELETE_ACCOUNT_FORM_ELEMENT)
            return
        }

        // Account deleted and logged out. Keep the loading state while the browser navigates away
        isRedirecting = true
        window.location.href = '/'
    } catch (error) {
        console.error(error)
        showErrorMessage(GENERIC_ERROR_MESSAGE, DELETE_ACCOUNT_FORM_ELEMENT)
    } finally {
        if (!isRedirecting) {
            toggleLoadingState(loaderId)
        }
    }
}

// Check one-time local-storage value and show success toast notification after submission page reload
const maybeShowSuccessToast = () => {
    if (localStorage.getItem('profileUpdated')) {
        setTimeout(() => {
            window.dispatchEvent(
                new CustomEvent('showtoast', {
                    detail: {
                        toastId: 'user-edited-toast-notification',
                        message: 'Profile updated'
                    }
                })
            )
            localStorage.removeItem('profileUpdated')
        }, 100);
    }
}

// Toggle profile image edit panel event
if (PROFILE_IMAGE_EDIT_TOGGLE && PROFILE_IMAGE_EDIT_TOGGLE instanceof HTMLElement) {
    PROFILE_IMAGE_EDIT_TOGGLE.addEventListener('click', toggleProfileImageEditPanel)
}

// Show profile image preview update when file selected
if (PROFILE_IMAGE_EDIT_INPUT_FIELD && PROFILE_IMAGE_EDIT_INPUT_FIELD instanceof HTMLElement) {
    PROFILE_IMAGE_EDIT_INPUT_FIELD.addEventListener('change', displayPreviewProfileImageUpdate)
}

// Event listener to remove preview
if (REMOVE_PROFILE_IMAGE_BUTTON && REMOVE_PROFILE_IMAGE_BUTTON instanceof HTMLElement) {
    REMOVE_PROFILE_IMAGE_BUTTON.addEventListener('click', removeProfileImage)
}

// Form submit event - prevent default to allow AJAX submission
if (PROFILE_UPDATE_FORM_ELEMENT && PROFILE_UPDATE_FORM_ELEMENT instanceof HTMLFormElement) {
    PROFILE_UPDATE_FORM_ELEMENT.addEventListener('submit', (e) => { 
        e.preventDefault(); 
        submitProfileEdit() 
    })
}

// Delete account form submit event - prevent default to allow AJAX submission
if (DELETE_ACCOUNT_FORM_ELEMENT && DELETE_ACCOUNT_FORM_ELEMENT instanceof HTMLFormElement) {
    DELETE_ACCOUNT_FORM_ELEMENT.addEventListener('submit', (e) => {
        e.preventDefault()
        submitDeleteAccount()
    })
}

// Show update success message after page reload
maybeShowSuccessToast()

// Reset forms if modal is closed
window.addEventListener('modalClosed', (e) => {
    if (PROFILE_IMAGE_EDIT_TOGGLE) {
        resetProfileEditForm(e)
    }
    clearErrors(DELETE_ACCOUNT_FORM_ELEMENT)
})