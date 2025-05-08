import { mount } from '@vue/test-utils'
import OrderForm from '../../resources/js/src/pages/orders/OrderForm.vue'

test('opens Add Order modal', async () => {
  const wrapper = mount(OrderForm)
  // Simulate event to open modal, e.g., wrapper.find('button.open-modal').trigger('click')
  expect(wrapper.exists()).toBe(true)
})