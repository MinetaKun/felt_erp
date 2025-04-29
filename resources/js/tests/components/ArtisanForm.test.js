import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import ArtisanForm from '../../src/pages/artisans/ArtisanForm.vue'
import axios from 'axios'

// Mock axios
jest.mock('axios')

// Mock SweetAlert2
jest.mock('sweetalert2', () => ({
  fire: jest.fn().mockResolvedValue({})
}))

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/artisans', name: 'artisans' },
    { path: '/artisans/:id', name: 'artisan-edit' }
  ]
})

describe('ArtisanForm.vue', () => {
  let wrapper

  const mockDepartments = [
    { id: 1, name: 'Department 1' },
    { id: 2, name: 'Department 2' }
  ]

  const mockArtisan = {
    id: 1,
    name: 'Test Artisan',
    email: 'test@example.com',
    phone_number: '1234567890',
    basic_salary: '1000',
    pan_number: 'ABCDE1234F',
    bank_account_number: '1234567890',
    department: { id: 1, name: 'Department 1' },
    status: 'active',
    skills: ['Skill 1', 'Skill 2'],
    profile_photo_url: 'profile.jpg',
    citizenship_photo_url: 'citizenship.jpg'
  }

  beforeEach(() => {
    // Reset axios mock
    axios.get.mockReset()
    axios.post.mockReset()

    // Mock department fetch
    axios.get.mockImplementation((url) => {
      if (url === '/departments') {
        return Promise.resolve({ data: mockDepartments })
      }
      if (url.includes('/artisans/')) {
        return Promise.resolve({ data: { data: mockArtisan } })
      }
      return Promise.reject(new Error('Not found'))
    })

    // Mock artisan creation/update
    axios.post.mockResolvedValue({ data: { message: 'Success' } })

    // Mount component
    wrapper = mount(ArtisanForm, {
      global: {
        plugins: [router],
        mocks: {
          $route: {
            params: {}
          }
        }
      }
    })
  })

  it('renders correctly', () => {
    expect(wrapper.find('h1').text()).toBe('Add New Artisan')
    expect(wrapper.find('form').exists()).toBe(true)
  })

  it('loads departments on mount', async () => {
    await wrapper.vm.$nextTick()
    expect(axios.get).toHaveBeenCalledWith('/departments')
    expect(wrapper.vm.departments).toEqual(mockDepartments)
  })

  it('loads artisan data when in edit mode', async () => {
    wrapper = mount(ArtisanForm, {
      global: {
        plugins: [router],
        mocks: {
          $route: {
            params: { id: 1 }
          }
        }
      }
    })

    await wrapper.vm.$nextTick()
    expect(axios.get).toHaveBeenCalledWith('/artisans/1')
    expect(wrapper.vm.form.name).toBe(mockArtisan.name)
    expect(wrapper.vm.form.email).toBe(mockArtisan.email)
  })

  it('adds and removes skills correctly', async () => {
    const newSkill = 'New Skill'
    wrapper.vm.newSkill = newSkill
    await wrapper.vm.addSkill()
    
    expect(wrapper.vm.form.skills).toContain(newSkill)
    expect(wrapper.vm.newSkill).toBe('')

    await wrapper.vm.removeSkill(0)
    expect(wrapper.vm.form.skills).not.toContain(newSkill)
  })

  it('handles file uploads correctly', async () => {
    const file = new File([''], 'test.jpg', { type: 'image/jpeg' })
    
    // Test profile photo upload
    await wrapper.vm.onProfilePhotoChange({ target: { files: [file] } })
    expect(wrapper.vm.form.profile_photo).toBe(file)

    // Test citizenship photo upload
    await wrapper.vm.onCitizenshipPhotoChange({ target: { files: [file] } })
    expect(wrapper.vm.form.citizenship_photo).toBe(file)
  })

  it('submits form data correctly for new artisan', async () => {
    const formData = {
      name: 'New Artisan',
      email: 'new@example.com',
      phone_number: '1234567890',
      basic_salary: '1000',
      pan_number: 'ABCDE1234F',
      bank_account_number: '1234567890',
      department_id: '1',
      status: 'active',
      skills: ['Skill 1']
    }

    wrapper.vm.form = { ...formData }
    await wrapper.vm.submitForm()

    expect(axios.post).toHaveBeenCalledWith(
      '/artisans',
      expect.any(FormData),
      expect.any(Object)
    )
  })

  it('submits form data correctly for existing artisan', async () => {
    wrapper = mount(ArtisanForm, {
      global: {
        plugins: [router],
        mocks: {
          $route: {
            params: { id: 1 }
          }
        }
      }
    })

    await wrapper.vm.$nextTick()
    await wrapper.vm.submitForm()

    expect(axios.post).toHaveBeenCalledWith(
      '/artisans/1',
      expect.any(FormData),
      expect.any(Object)
    )
  })

  it('handles form submission errors correctly', async () => {
    const errorMessage = 'Validation failed'
    axios.post.mockRejectedValue({
      response: {
        data: {
          message: errorMessage
        }
      }
    })

    await wrapper.vm.submitForm()
    expect(wrapper.vm.loading).toBe(false)
  })
}) 