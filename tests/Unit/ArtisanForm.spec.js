import { mount } from '@vue/test-utils'
import ArtisanForm from '../../resources/js/src/pages/artisans/ArtisanForm.vue'

test('opens Add Artisan form', async () => {
  const wrapper = mount(ArtisanForm, {
    global: {
      mocks: {
        $route: {
          params: {id:'1'} // or { id: 'some-id' } if you want to test edit mode
        }
      }
    }
  })
  expect(wrapper.exists()).toBe(true)
})