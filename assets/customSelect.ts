/**
 * Custom Select Component
 * Creates styled select dropdowns that work with native select elements
 */

class CustomSelect {
    private wrapper: HTMLElement;
    private nativeSelect: HTMLSelectElement;
    private trigger: HTMLButtonElement;
    private valueDisplay: HTMLElement;
    private dropdown: HTMLElement;
    private options: NodeListOf<HTMLElement>;
    private arrow: HTMLElement;
    private isOpen: boolean = false;

    constructor(wrapper: HTMLElement) {
        this.wrapper = wrapper;
        this.nativeSelect = wrapper.querySelector('.js-custom-select-native') as HTMLSelectElement;
        this.trigger = wrapper.querySelector('.js-custom-select-trigger') as HTMLButtonElement;
        this.valueDisplay = wrapper.querySelector('.js-custom-select-value') as HTMLElement;
        this.dropdown = wrapper.querySelector('.js-custom-select-dropdown') as HTMLElement;
        this.options = wrapper.querySelectorAll('.js-custom-select-option');
        this.arrow = wrapper.querySelector('.js-custom-select-arrow') as HTMLElement;

        if (!this.nativeSelect || !this.trigger || !this.valueDisplay || !this.dropdown || !this.options) {
            console.error('Custom select: Missing required elements');
            return;
        }

        this.init();
    }

    private init(): void {
        // Set initial value from native select
        this.updateDisplayValue();

        // Bind event listeners
        this.trigger.addEventListener('click', () => this.toggle());
        
        this.options.forEach(option => {
            option.addEventListener('click', () => {
                const value = option.getAttribute('data-value');
                if (value !== null) {
                    this.selectOption(value, option.textContent || '');
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => this.handleOutsideClick(e));

        // Handle keyboard navigation
        this.trigger.addEventListener('keydown', (e) => this.handleKeyboard(e));

        // Sync with native select changes (for programmatic updates)
        this.nativeSelect.addEventListener('change', () => {
            this.updateDisplayValue();
        });
    }

    private toggle(): void {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    private open(): void {
        this.isOpen = true;
        this.dropdown.classList.remove('hidden');
        this.trigger.setAttribute('aria-expanded', 'true');
        this.arrow.style.transform = 'rotate(180deg)';
        this.highlightSelectedOption();
    }

    private close(): void {
        this.isOpen = false;
        this.dropdown.classList.add('hidden');
        this.trigger.setAttribute('aria-expanded', 'false');
        this.arrow.style.transform = 'rotate(0deg)';
    }

    private selectOption(value: string, label: string): void {
        // Update native select
        this.nativeSelect.value = value;
        
        // Trigger change event on native select for form handling
        const event = new Event('change', { bubbles: true });
        this.nativeSelect.dispatchEvent(event);

        // Update display
        this.valueDisplay.textContent = label;
        
        // Update selected state visually
        this.updateSelectedState(value);

        // Close dropdown
        this.close();
    }

    private updateDisplayValue(): void {
        const selectedOption = this.nativeSelect.selectedOptions[0];
        if (selectedOption) {
            this.valueDisplay.textContent = selectedOption.textContent || '';
            this.updateSelectedState(selectedOption.value);
        }
    }

    private updateSelectedState(value: string): void {
        this.options.forEach(option => {
            const optionValue = option.getAttribute('data-value');
            if (optionValue === value) {
                option.classList.add('bg-indigo-50', 'dark:bg-indigo-900/30', 'font-medium');
                option.setAttribute('aria-selected', 'true');
            } else {
                option.classList.remove('bg-indigo-50', 'dark:bg-indigo-900/30', 'font-medium');
                option.setAttribute('aria-selected', 'false');
            }
        });
    }

    private highlightSelectedOption(): void {
        const selectedValue = this.nativeSelect.value;
        this.options.forEach(option => {
            const optionValue = option.getAttribute('data-value');
            if (optionValue === selectedValue) {
                option.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    private handleOutsideClick(event: Event): void {
        const target = event.target as Node;
        if (!this.wrapper.contains(target) && this.isOpen) {
            this.close();
        }
    }

    private handleKeyboard(event: KeyboardEvent): void {
        switch (event.key) {
            case 'Enter':
            case ' ':
                event.preventDefault();
                this.toggle();
                break;
            case 'Escape':
                if (this.isOpen) {
                    event.preventDefault();
                    this.close();
                }
                break;
            case 'ArrowDown':
                event.preventDefault();
                if (!this.isOpen) {
                    this.open();
                } else {
                    this.navigateOptions('down');
                }
                break;
            case 'ArrowUp':
                event.preventDefault();
                if (!this.isOpen) {
                    this.open();
                } else {
                    this.navigateOptions('up');
                }
                break;
        }
    }

    private navigateOptions(direction: 'up' | 'down'): void {
        const currentIndex = Array.from(this.nativeSelect.options).findIndex(
            option => option.selected
        );
        
        let newIndex = direction === 'down' ? currentIndex + 1 : currentIndex - 1;
        
        // Wrap around
        if (newIndex >= this.nativeSelect.options.length) {
            newIndex = 0;
        } else if (newIndex < 0) {
            newIndex = this.nativeSelect.options.length - 1;
        }

        const newOption = this.nativeSelect.options[newIndex];
        if (newOption) {
            this.selectOption(newOption.value, newOption.textContent || '');
        }
    }
}

/**
 * Initialize all custom selects on the page
 */
export function initCustomSelects(): void {
    const wrappers = document.querySelectorAll('.js-custom-select-wrapper');
    wrappers.forEach(wrapper => {
        new CustomSelect(wrapper as HTMLElement);
    });
}
