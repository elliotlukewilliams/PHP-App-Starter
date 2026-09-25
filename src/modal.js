// Elements that can receive keyboard focus
const FOCUSABLE_SELECTOR = [
    'a[href]',
    'button:not([disabled]):not([tabindex="-1"])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])'
].join(',')

// The element that opened each modal, so focus can return to it on close
const modalTriggers = new Map()

const getFocusableElements = (modal) =>
    Array.from(modal.querySelectorAll(FOCUSABLE_SELECTOR))
        .filter(element => element.offsetParent !== null) // Visible elements only

// Label the dialog with its heading so screen readers announce the title when it opens
const labelModal = (modal) => {
    if (modal.hasAttribute('aria-labelledby')) {
        return
    }
    const heading = modal.querySelector('h1, h2, h3')
    if (!heading) {
        return
    }
    if (!heading.id) {
        heading.id = `${modal.id}-title`
    }
    modal.setAttribute('aria-labelledby', heading.id)
}

const openModal = (modalId, trigger = document.activeElement) => {
    const modal = document.getElementById(modalId)
    if (!modal) {
        return
    }
    labelModal(modal)
    modalTriggers.set(modalId, trigger)

    modal.classList.remove('hidden')
    modal.ariaHidden = 'false'
    document.body.classList.add('overflow-hidden')

    // Move focus into the modal.
    const preferred = modal.querySelector('[data-autofocus]')
    const [firstFocusable] = getFocusableElements(modal)
    ;(preferred ?? firstFocusable)?.focus()
}

const closeModal = (modalId) => {
    const modal = document.getElementById(modalId)
    if (!modal || modal.classList.contains('hidden')) {
        return
    }
    modal.classList.add('hidden')
    modal.ariaHidden = 'true'
    document.body.classList.remove('overflow-hidden')

    // Return focus to the element that opened the modal
    const trigger = modalTriggers.get(modalId)
    if (trigger instanceof HTMLElement && document.contains(trigger)) {
        trigger.focus()
    }
    modalTriggers.delete(modalId)

    // Fire custom event when modal is closed
    window.dispatchEvent(
        new CustomEvent('modalClosed', {
            detail: {
                modalId: modal.id
            }
        })
    )
}

const getOpenModal = () => document.querySelector('.modal:not(.hidden)')

const modalCloseButtons = document.querySelectorAll('.close-modal')
if (modalCloseButtons) {
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const modal = event.currentTarget.closest('.modal')
            if (modal && modal.id) {
                closeModal(modal.id)
            }
        })
    })
}

const modalTriggerButtons = document.querySelectorAll('[data-modal-trigger]')
if (modalTriggerButtons) {
    modalTriggerButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const modalId = event.currentTarget.dataset.modalTrigger
            if (modalId) {
                openModal(modalId, event.currentTarget)
            }
        })
    })
}

// Keyboard support: Escape closes the open modal, Tab/Shift+Tab stay inside it
document.addEventListener('keydown', (event) => {
    const modal = getOpenModal()
    if (!modal) {
        return
    }

    if (event.key === 'Escape') {
        event.preventDefault()
        closeModal(modal.id)
        return
    }

    if (event.key !== 'Tab') {
        return
    }
    const focusableElements = getFocusableElements(modal)
    if (focusableElements.length === 0) {
        event.preventDefault()
        return
    }
    const first = focusableElements[0]
    const last = focusableElements[focusableElements.length - 1]
    const focusIsOutside = !modal.contains(document.activeElement)

    if (event.shiftKey && (document.activeElement === first || focusIsOutside)) {
        event.preventDefault()
        last.focus()
    } else if (!event.shiftKey && (document.activeElement === last || focusIsOutside)) {
        event.preventDefault()
        first.focus()
    }
})

// Custom event listener to close modal programmatically
window.addEventListener('closeModal', (event) => {
    const { modalId } = event.detail || {}
    closeModal(modalId)
})
