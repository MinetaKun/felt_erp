import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import ProductivityDashboard from '@/components/ProductivityDashboard.vue'
import axios from 'axios'

vi.mock('axios')

describe('ProductivityDashboard', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(ProductivityDashboard, {
            global: {
                mocks: {
                    $axios: axios
                }
            }
        })
    })

    it('displays progress bars for different metrics', () => {
        const progressBars = wrapper.findAll('.progress-bar')
        expect(progressBars).toHaveLength(4) // Assuming 4 metrics: attendance, productivity, quality, efficiency
    })

    it('updates progress bars with real-time data', async () => {
        // Mock real-time data
        const mockData = {
            attendance: 95,
            productivity: 88,
            quality: 92,
            efficiency: 90
        }

        // Mock WebSocket or polling response
        axios.get.mockResolvedValueOnce({ data: mockData })

        // Trigger data update
        await wrapper.vm.fetchProductivityData()

        // Verify progress bars are updated
        expect(wrapper.find('.attendance-progress').attributes('style')).toContain('width: 95%')
        expect(wrapper.find('.productivity-progress').attributes('style')).toContain('width: 88%')
        expect(wrapper.find('.quality-progress').attributes('style')).toContain('width: 92%')
        expect(wrapper.find('.efficiency-progress').attributes('style')).toContain('width: 90%')
    })

    it('displays productivity trends chart', () => {
        const chart = wrapper.find('.productivity-chart')
        expect(chart.exists()).toBe(true)
    })

    it('updates productivity trends with new data', async () => {
        const mockTrendData = {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            datasets: [{
                label: 'Productivity',
                data: [85, 88, 92, 90, 95]
            }]
        }

        axios.get.mockResolvedValueOnce({ data: mockTrendData })
        await wrapper.vm.fetchTrendData()

        expect(wrapper.vm.chartData).toEqual(mockTrendData)
    })

    it('shows alerts for low productivity', async () => {
        const mockData = {
            attendance: 95,
            productivity: 65, // Below threshold
            quality: 92,
            efficiency: 90
        }

        axios.get.mockResolvedValueOnce({ data: mockData })
        await wrapper.vm.fetchProductivityData()

        expect(wrapper.find('.alert-warning').exists()).toBe(true)
        expect(wrapper.text()).toContain('Productivity is below target')
    })
}) 