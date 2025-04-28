import { mount } from '@vue/test-utils';
import ExampleComponent from '../ExampleComponent.vue';

describe('ExampleComponent', () => {
    it('renders correctly', () => {
        const wrapper = mount(ExampleComponent);
        expect(wrapper.exists()).toBe(true);
    });

    it('displays the correct text', () => {
        const wrapper = mount(ExampleComponent, {
            props: {
                message: 'Hello World'
            }
        });
        expect(wrapper.text()).toContain('Hello World');
    });
}); 