import emitter from 'tiny-emitter/instance';
import { useToast } from 'vue-toastification';

const useModalToast = () => {
    const toast = useToast();
    // valid value of 'data' function argument
    // value should be null if want to render the default data
    // {
    // 	message: string // any text to show,
    // 	actionButton: {
    // 		class: string // action button class,
    // 		text: string // any text to render on the action button,
    // 	},
    // 	returnButton: {
    // 		class: string // action button class,
    // 		text: string // any text to render on the return button,
    // 	},
    // }
    const showConfirmModal = (data, callback) => {
        emitter.emit('show-confirm-modal', data, callback);
    };

    const showToast = (data) => {
        if (typeof data === 'string') {
            toast.success(data);
        } else if (typeof data === 'object') {
            const { type = 'success', message, duration = 3000 } = data;
            toast[type](message, { timeout: duration });
        }
    };

    return {
        showConfirmModal,
        showToast,
    };
};

export default useModalToast;
