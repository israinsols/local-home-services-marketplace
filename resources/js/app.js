import './bootstrap';

document.querySelectorAll('[data-lead-stepper]').forEach((form) => {
    const steps = Array.from(form.querySelectorAll('[data-lead-step]'));

    if (steps.length === 0) {
        return;
    }

    let currentStep = Math.min(Math.max(Number(form.dataset.initialStep) || 1, 1), steps.length);
    const progressItems = Array.from(form.querySelectorAll('[data-progress-step]'));
    const stepCount = form.querySelector('[data-step-count]');

    form.classList.add('is-enhanced');
    form.noValidate = true;

    const updateReview = () => {
        form.querySelectorAll('[data-review-value]').forEach((summary) => {
            const field = form.querySelector(`[name="${summary.dataset.reviewValue}"]`);
            const value = field instanceof HTMLSelectElement
                ? (field.value ? field.selectedOptions[0]?.textContent?.trim() : '')
                : field?.value?.trim();

            summary.textContent = value || 'Not provided';
        });
    };

    const showStep = (stepNumber, shouldFocus = true) => {
        currentStep = Math.min(Math.max(stepNumber, 1), steps.length);

        steps.forEach((step, index) => {
            step.hidden = index + 1 !== currentStep;
        });

        progressItems.forEach((item, index) => {
            if (index + 1 === currentStep) {
                item.setAttribute('aria-current', 'step');
            } else {
                item.removeAttribute('aria-current');
            }

            item.dataset.complete = index + 1 < currentStep ? 'true' : 'false';
        });

        if (stepCount) {
            stepCount.textContent = `Step ${currentStep} of ${steps.length}`;
            stepCount.hidden = false;
        }

        if (currentStep === steps.length) {
            updateReview();
        }

        if (shouldFocus) {
            steps[currentStep - 1].querySelector('[data-step-title]')?.focus();
        }
    };

    const validateCurrentStep = () => {
        const invalidField = Array.from(steps[currentStep - 1].querySelectorAll('input, select, textarea'))
            .find((field) => !field.disabled && !field.checkValidity());

        if (!invalidField) {
            return true;
        }

        invalidField.reportValidity();
        invalidField.focus();

        return false;
    };

    form.querySelectorAll('[data-step-next]').forEach((button) => {
        button.addEventListener('click', () => {
            if (validateCurrentStep()) {
                showStep(currentStep + 1);
            }
        });
    });

    form.querySelectorAll('[data-step-back]').forEach((button) => {
        button.addEventListener('click', () => showStep(currentStep - 1));
    });

    form.addEventListener('input', updateReview);
    form.addEventListener('change', updateReview);
    form.addEventListener('submit', (event) => {
        if (!validateCurrentStep()) {
            event.preventDefault();
            return;
        }

        if (currentStep < steps.length) {
            event.preventDefault();
            showStep(currentStep + 1);
        }
    });

    showStep(currentStep, false);
});
