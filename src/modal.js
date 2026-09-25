const openModal = (modalId) => {
    const modal = document.getElementById(modalId)
    if (!modal) {
        return
    }
    modal.classList.remove('hidden')
    modal.ariaHidden = false
    document.body.classList.add('overflow-hidden')
}

const closeModal = (modalId) => {
    const modal = document.getElementById(modalId)
    if (!modal) {
        return
    }
    modal.classList.add('hidden')
    modal.ariaHidden = true
    document.body.classList.remove('overflow-hidden')

    // Fire custom event when modal is closed
    window.dispatchEvent(
        new CustomEvent('modalClosed', {
            detail: {
                modalId: modal.id
            }
        })
    )
}

const modalCloseButtons = document.querySelectorAll('.close-modal')
if (modalCloseButtons) {
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const modal = event.target.closest('.modal')
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
                openModal(modalId)
            }
        })
    })
}

// Custom event listener to close modal programmatically
window.addEventListener('closeModal', (event) => {
    const { modalId } = event.detail || {}
    closeModal(modalId)
})