import { mount } from '@vue/test-utils'
import PettyCashDashboard from '../../resources/js/src/pages/petty-cash/Dashboard.vue'
import axios from 'axios'

vi.mock('axios')

test('export button triggers CSV download', async () => {
  axios.get.mockResolvedValue({ data: { /* mock your expected data here */ } })

  const wrapper = mount(PettyCashDashboard, {
    global: {
      stubs: ['router-link'],
      mocks: {
        $route: { params: {} }
      }
    }
  })

  await wrapper.vm.$nextTick()
  console.log(wrapper.html()) // <-- Add this line

  const exportBtn = wrapper.find('button.export-csv')
  expect(exportBtn.exists()).toBe(true)
  await exportBtn.trigger('click')
  expect(wrapper.emitted('csv-export')).toBeTruthy()
})