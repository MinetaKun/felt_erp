import { mount } from '@vue/test-utils'
import MarkAttendance from '../../src/pages/attendance/MarkAttendance.vue'
import axios from 'axios'
import Swal from 'sweetalert2'

// Mock axios
jest.mock('axios')

// Mock SweetAlert2
jest.mock('sweetalert2', () => ({
  fire: jest.fn().mockResolvedValue({})
}))

describe('MarkAttendance.vue', () => {
  let wrapper

  const mockDepartments = [
    { id: 1, name: 'Department 1' },
    { id: 2, name: 'Department 2' }
  ]

  const mockUsers = [
    { id: 1, name: 'User 1', department_id: 1, type: 'user' },
    { id: 2, name: 'User 2', department_id: 2, type: 'user' }
  ]

  const mockArtisans = [
    { id: 3, name: 'Artisan 1', department_id: 1, type: 'artisan' },
    { id: 4, name: 'Artisan 2', department_id: 2, type: 'artisan' }
  ]

  const mockExistingAttendance = [
    {
      id: 1,
      name: 'User 1',
      type: 'user',
      department: 'Department 1',
      status: 'present',
      remarks: 'On time'
    }
  ]

  beforeEach(() => {
    // Reset axios mock
    axios.get.mockReset()
    axios.post.mockReset()
    axios.put.mockReset()

    // Mock API responses
    axios.get.mockImplementation((url) => {
      if (url === '/departments') {
        return Promise.resolve({ data: mockDepartments })
      }
      if (url === '/users') {
        return Promise.resolve({ data: { data: mockUsers } })
      }
      if (url === '/artisans') {
        return Promise.resolve({ data: { data: mockArtisans } })
      }
      if (url.includes('/attendance/check-date/')) {
        return Promise.resolve({ data: { exists: false } })
      }
      if (url.includes('/attendance/date/')) {
        return Promise.resolve({ data: mockExistingAttendance })
      }
      return Promise.reject(new Error('Not found'))
    })

    // Mock attendance creation/update
    axios.post.mockResolvedValue({ data: { message: 'Success' } })
    axios.put.mockResolvedValue({ data: { message: 'Success' } })

    // Mount component
    wrapper = mount(MarkAttendance)
  })

  it('renders correctly', () => {
    expect(wrapper.find('h3').text()).toBe('Mark Daily Attendance')
    expect(wrapper.find('form').exists()).toBe(true)
  })

  it('loads departments, users, and artisans on mount', async () => {
    await wrapper.vm.$nextTick()
    expect(axios.get).toHaveBeenCalledWith('/departments')
    expect(axios.get).toHaveBeenCalledWith('/users')
    expect(axios.get).toHaveBeenCalledWith('/artisans')
  })

  it('filters people correctly based on department and type', async () => {
    await wrapper.vm.$nextTick()
    
    wrapper.vm.selectedDepartment = '1'
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.filteredPeople.length).toBe(2)
    expect(wrapper.vm.filteredPeople.every(p => p.department_id === 1)).toBe(true)

    wrapper.vm.selectedType = 'user'
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.filteredPeople.length).toBe(1)
    expect(wrapper.vm.filteredPeople[0].type).toBe('user')
  })

  it('handles search functionality correctly', async () => {
    await wrapper.vm.$nextTick()
    
    wrapper.vm.searchQuery = 'User 1'
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.filteredPeople.length).toBe(1)
    expect(wrapper.vm.filteredPeople[0].name).toBe('User 1')
  })

  it('marks attendance for selected people', async () => {
    await wrapper.vm.$nextTick()
    
    wrapper.vm.selectedPeople = [1, 3]
    await wrapper.vm.markSelected('present')
    
    expect(wrapper.vm.attendanceData[1].status).toBe('present')
    expect(wrapper.vm.attendanceData[3].status).toBe('present')
  })

  it('saves attendance correctly', async () => {
    await wrapper.vm.$nextTick()
    
    wrapper.vm.attendanceData = {
      1: { status: 'present', remarks: 'On time', type: 'user' },
      3: { status: 'late', remarks: 'Traffic', type: 'artisan' }
    }
    
    await wrapper.vm.saveAttendance()
    
    expect(axios.post).toHaveBeenCalledWith('/attendance/bulk', {
      records: expect.arrayContaining([
        expect.objectContaining({
          attendanceable_id: 1,
          attendanceable_type: 'App\\Models\\User',
          status: 'present'
        }),
        expect.objectContaining({
          attendanceable_id: 3,
          attendanceable_type: 'App\\Models\\Artisan',
          status: 'late'
        })
      ])
    })
  })

  it('handles existing attendance correctly', async () => {
    axios.get.mockImplementationOnce((url) => {
      if (url.includes('/attendance/check-date/')) {
        return Promise.resolve({ data: { exists: true } })
      }
      return Promise.reject(new Error('Not found'))
    })

    await wrapper.vm.checkExistingAttendance(wrapper.vm.selectedDate)
    expect(wrapper.vm.existingAttendance).toBe(true)
  })

  it('updates existing attendance correctly', async () => {
    await wrapper.vm.loadExistingAttendance()
    await wrapper.vm.updateExistingAttendance()
    
    expect(axios.put).toHaveBeenCalledWith(
      `/attendance/date/${wrapper.vm.selectedDate}`,
      { records: mockExistingAttendance }
    )
  })

  it('handles bulk actions correctly', async () => {
    await wrapper.vm.$nextTick()
    
    wrapper.vm.selectedPeople = [1, 2, 3, 4]
    await wrapper.vm.markSelected('absent')
    
    expect(wrapper.vm.attendanceData[1].status).toBe('absent')
    expect(wrapper.vm.attendanceData[2].status).toBe('absent')
    expect(wrapper.vm.attendanceData[3].status).toBe('absent')
    expect(wrapper.vm.attendanceData[4].status).toBe('absent')
  })

  it('toggles select all functionality correctly', async () => {
    await wrapper.vm.$nextTick()
    
    wrapper.vm.toggleSelectAll()
    expect(wrapper.vm.selectedPeople.length).toBe(4)
    
    wrapper.vm.toggleSelectAll()
    expect(wrapper.vm.selectedPeople.length).toBe(0)
  })

  it('handles error states correctly', async () => {
    axios.post.mockRejectedValueOnce({
      response: {
        data: {
          message: 'Error saving attendance'
        }
      }
    })

    await wrapper.vm.saveAttendance()
    expect(wrapper.vm.loading).toBe(false)
  })
}) 