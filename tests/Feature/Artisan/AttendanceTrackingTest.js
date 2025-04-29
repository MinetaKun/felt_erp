import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import AttendanceTracker from '@/components/AttendanceTracker.vue'
import axios from 'axios'

vi.mock('axios')

describe('AttendanceTracker', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(AttendanceTracker, {
            global: {
                mocks: {
                    $axios: axios
                }
            }
        })
    })

    it('displays attendance list for the current day', async () => {
        const mockAttendance = [
            { id: 1, name: 'John Doe', status: 'present', check_in: '09:00', check_out: '17:00' },
            { id: 2, name: 'Jane Smith', status: 'absent', check_in: null, check_out: null }
        ]

        axios.get.mockResolvedValueOnce({ data: mockAttendance })
        await wrapper.vm.fetchTodayAttendance()

        const attendanceRows = wrapper.findAll('.attendance-row')
        expect(attendanceRows).toHaveLength(2)
        expect(wrapper.text()).toContain('John Doe')
        expect(wrapper.text()).toContain('Jane Smith')
    })

    it('can mark attendance for an artisan', async () => {
        const mockResponse = { 
            data: { 
                message: 'Attendance marked successfully',
                attendance: {
                    id: 1,
                    artisan_id: 1,
                    status: 'present',
                    check_in: '09:00'
                }
            }
        }

        axios.post.mockResolvedValueOnce(mockResponse)

        await wrapper.vm.markAttendance(1, 'present')
        
        expect(axios.post).toHaveBeenCalledWith('/api/attendance', {
            artisan_id: 1,
            status: 'present'
        })
        expect(wrapper.text()).toContain('Attendance marked successfully')
    })

    it('can generate attendance report', async () => {
        const mockReportData = {
            start_date: '2024-01-01',
            end_date: '2024-01-31',
            data: [
                { date: '2024-01-01', present: 45, absent: 5, late: 2 },
                { date: '2024-01-02', present: 48, absent: 2, late: 0 }
            ]
        }

        axios.get.mockResolvedValueOnce({ data: mockReportData })
        await wrapper.vm.generateReport('2024-01-01', '2024-01-31')

        expect(wrapper.vm.reportData).toEqual(mockReportData)
        expect(wrapper.find('.report-table').exists()).toBe(true)
    })

    it('can export attendance report to Excel', async () => {
        const mockBlob = new Blob(['test'], { type: 'application/vnd.ms-excel' })
        axios.get.mockResolvedValueOnce({ 
            data: mockBlob,
            headers: {
                'content-type': 'application/vnd.ms-excel',
                'content-disposition': 'attachment; filename=attendance-report.xlsx'
            }
        })

        await wrapper.vm.exportReport('2024-01-01', '2024-01-31', 'excel')

        expect(axios.get).toHaveBeenCalledWith('/api/attendance/export', {
            params: {
                start_date: '2024-01-01',
                end_date: '2024-01-31',
                format: 'excel'
            },
            responseType: 'blob'
        })
    })

    it('shows attendance statistics', async () => {
        const mockStats = {
            total_present: 45,
            total_absent: 5,
            total_late: 2,
            attendance_rate: 86.5
        }

        axios.get.mockResolvedValueOnce({ data: mockStats })
        await wrapper.vm.fetchAttendanceStats()

        expect(wrapper.find('.attendance-rate').text()).toContain('86.5%')
        expect(wrapper.find('.total-present').text()).toContain('45')
        expect(wrapper.find('.total-absent').text()).toContain('5')
        expect(wrapper.find('.total-late').text()).toContain('2')
    })
}) 