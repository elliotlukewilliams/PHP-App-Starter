/**
 * GLOBAL SCOPE
 */
// DOM elements
const PROFILE_UPDATE_FORM_ELEMENT = document.getElementById('profile-edit-form')
const PROFILE_IMAGE_EDIT_TOGGLE = document.getElementById('profile-image-edit-panel-toggle')
const PROFILE_IMAGE_EDIT_INPUT_FIELD = document.getElementById('profile-edit-image')
const REMOVE_PROFILE_IMAGE_BUTTON = document.getElementById('remove-profile-image')

// Reset state for form data
let OLD_PROFILE_UPDATE_FORM_DATA = 
    (PROFILE_UPDATE_FORM_ELEMENT && PROFILE_UPDATE_FORM_ELEMENT instanceof HTMLFormElement) ?
    new FormData(PROFILE_UPDATE_FORM_ELEMENT) :
    null

// Track if profile image ha sbeen updated
let PROFILE_IMAGE_UDATED = false

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
}

const toggleLoadingState = () => {
    const loader = document.getElementById('profile-edit-loader')
    if (loader) {
        loader.classList.toggle('hidden')

        if (loader.classList.contains('hidden')) {
            loader.ariaHidden = 'true'
        } else {
            loader.ariaHidden = 'false'
        }
    }
}

const showErrorMessage = (errorMessage) => {
    const errorOutput = PROFILE_UPDATE_FORM_ELEMENT.querySelector('output')
    if (!errorOutput) return

    errorOutput.classList.add('active')
    errorOutput.innerHTML = errorMessage
}

const clearErrors = () => {
    PROFILE_UPDATE_FORM_ELEMENT.querySelectorAll("output.active").forEach(error => {
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
    if (file.size > 3000000) {
        showErrorMessage('File size too large. Profile images must be no larger than 3MB')
        PROFILE_IMAGE_EDIT_INPUT_FIELD.value = OLD_PROFILE_UPDATE_FORM_DATA.get('image_url')
        return
    }

    PROFILE_IMAGE_UDATED = true

    // Check/create image preview in DOM and apply source from reader
    const reader = new FileReader()
    reader.onload = () => {
        let image = profilePictureContainer.querySelector('img.new-profile-image-preview')
        if (!image) {
            image = document.createElement('img')
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
    if (newProfileImagePreview && originalProfileImage) {
        newProfileImagePreview.remove()
        originalProfileImage.classList.remove('hidden')
    } else if (originalProfileImage && imageFileInputElement) {
        originalProfileImage.classList.add('hidden')
        imageFileInputElement.value = ''
        PROFILE_IMAGE_UDATED = true
    }
    
    // Close profile image edit panel
    toggleProfileImageEditPanel()
}

// Reset for and element states
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
    const originalProileImagePreview = modalParentElement.querySelector('.profile-image-preview')
    if (originalProileImagePreview && originalProileImagePreview.classList.contains('hidden')) {
        originalProileImagePreview.classList.remove('hidden')
    }
    const newProfileImagePreview = modalParentElement.querySelector('.new-profile-image-preview')
    if (newProfileImagePreview) {
        newProfileImagePreview.remove()
    }

    // Clear errors
    clearErrors()
}

// Submit profile edit
const submitProfileEdit = async () => {
    // Prepare two sets of form data to be forked and later compared
    const submittedFormData = new FormData(PROFILE_UPDATE_FORM_ELEMENT)
    const filteredFormData = submittedFormData
    try {
        toggleLoadingState();
        clearErrors()

        // Filter out data that hasn't changed to minimise payload
        for (const pair of submittedFormData.entries()) {
            const fieldKey = pair[0]
            const fieldValue = pair[1]
            if (fieldValue === OLD_PROFILE_UPDATE_FORM_DATA.get(fieldKey)) {
                filteredFormData.delete(fieldKey)
            }
        }
        
        // Also remove image file from payload if not updated
        if (!PROFILE_IMAGE_UDATED) {
            filteredFormData.delete('image_file')
        }

        // If no changed fields, show notice and exit
        const allUpdatedTextFields = Array.from(filteredFormData.keys()).filter((key) => key !== 'image_file')
        if (allUpdatedTextFields.length === 0 && !PROFILE_IMAGE_UDATED) {
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
        window.location.reload('/')
    } catch (error) {
        console.error(error)
    } finally {
        toggleLoadingState();
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

// Show update success message after page reload
maybeShowSuccessToast()

// Reset form if modal is closed
window.addEventListener('modalClosed', (e) => { resetProfileEditForm(e) })