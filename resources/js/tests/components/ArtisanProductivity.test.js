import { mount } from '@vue/test-utils'
import ArtisanProductivity from '../../src/pages/artisans/ArtisanProductivity.vue'
import axios from 'axios'

// Mock axios
jest.mock('axios')

describe('ArtisanProductivity.vue', () => {
  let wrapper

  const mockProductivityData = {
    data: [
      {
        id: 1,
        name: 'Test Artisan',
        department: { name: 'Department 1' },
        attendance: {
          present_days: 20,
          total_days: 25,
          attendance_rate: 80
        },
        orders: {
          completed_assignments: 15,
          total_assignments: 20,
          approved_assignments: 12,
          rejected_assignments: 3,
          completion_rate: 75,
          approval_rate: 80,
          rejection_rate: 20
        },
        products: {
          total_assigned: 30,
          total_completed: 25,
          total_approved: 22,
          total_rejected: 3,
          average_per_day: 2.5
        }
      }
    ],
    meta: {
      total_artisans: 1,
      total_products: 25,
      average_attendance_rate: 80,
      average_completion_rate: 75
    }
  }

  const mockDepartments = [
    { id: 1, name: 'Department 1' },
    { id: 2, name: 'Department 2' }
  ]

  beforeEach(() => {
    // Reset axios mock
    axios.get.mockReset()

    // Mock API responses
    axios.get.mockImplementation((url) => {
      if (url === '/departments') {
        return Promise.resolve({ data: mockDepartments })
      }
      if (url === '/artisans/productivity') {
        return Promise.resolve(mockProductivityData)
      }
      return Promise.reject(new Error('Not found'))
    })

    // Mount component
    wrapper = mount(ArtisanProductivity)
  })

  it('renders correctly', () => {
    expect(wrapper.find('h3').text()).toBe('Artisan Productivity')
    expect(wrapper.find('table').exists()).toBe(true)
  })

  it('loads departments and productivity data on mount', async () => {
    await wrapper.vm.$nextTick()
    expect(axios.get).toHaveBeenCalledWith('/departments')
    expect(axios.get).toHaveBeenCalledWith('/artisans/productivity', expect.any(Object))
  })

  it('displays correct statistics', async () => {
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.totalArtisans).toBe(1)
    expect(wrapper.vm.totalProducts).toBe(25)
    expect(wrapper.vm.averageAttendanceRate).toBe(80)
    expect(wrapper.vm.averageCompletionRate).toBe(75)
  })

  it('filters data when department changes', async () => {
    await wrapper.vm.$nextTick()
    wrapper.vm.selectedDepartment = '1'
    await wrapper.vm.fetchProductivity()
    
    expect(axios.get).toHaveBeenCalledWith('/artisans/productivity', {
      params: expect.objectContaining({
        department: '1'
      })
    })
  })

  it('filters data when date range changes', async () => {
    await wrapper.vm.$nextTick()
    wrapper.vm.dateRange = 'week'
    await wrapper.vm.fetchProductivity()
    
    expect(axios.get).toHaveBeenCalledWith('/artisans/productivity', {
      params: expect.objectContaining({
        date_range: 'week'
      })
    })
  })

  it('handles export functionality', async () => {
    const mockBlob = new Blob(['test'], { type: 'text/csv' })
    axios.get.mockResolvedValueOnce({ data: mockBlob })

    await wrapper.vm.exportData()
    
    expect(axios.get).toHaveBeenCalledWith('/artisans/productivity/export', {
      params: expect.any(Object),
      responseType: 'blob'
    })
  })

  it('handles loading state correctly', async () => {
    wrapper.vm.loading = true
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
    expect(wrapper.find('table').exists()).toBe(false)
  })

  it('handles error state correctly', async () => {
    wrapper.vm.error = true
    wrapper.vm.errorMessage = 'Test error message'
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.text-red-700').text()).toBe('Test error message')
    expect(wrapper.find('table').exists()).toBe(false)
  })

  it('calculates pagination correctly', async () => {
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.totalPages).toBe(1)
    
    wrapper.vm.productivityData = Array(15).fill({})
    wrapper.vm.itemsPerPage = 10
    expect(wrapper.vm.totalPages).toBe(2)
  })

  it('applies correct department colors', () => {
    expect(wrapper.vm.getDepartmentColor('Pottery')).toBe('bg-amber-100 text-amber-800')
    expect(wrapper.vm.getDepartmentColor('Weaving')).toBe('bg-emerald-100 text-emerald-800')
    expect(wrapper.vm.getDepartmentColor('Unknown')).toBe('bg-gray-100 text-gray-800')
  })
}) 