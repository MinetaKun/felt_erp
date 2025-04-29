import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import ArtisanForm from '@/components/ArtisanForm.vue'
import axios from 'axios'

vi.mock('axios')

describe('ArtisanForm', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(ArtisanForm, {
            global: {
                mocks: {
                    $axios: axios
                }
            }
        })
    })

    it('can upload profile photo', async () => {
        const file = new File(['test'], 'profile.jpg', { type: 'image/jpeg' })
        const input = wrapper.find('input[type="file"][name="profile_photo"]')
        
        await input.trigger('change', {
            target: {
                files: [file]
            }
        })

        expect(wrapper.vm.form.profile_photo).toBe(file)
    })

    it('can upload citizenship photo', async () => {
        const file = new File(['test'], 'citizenship.jpg', { type: 'image/jpeg' })
        const input = wrapper.find('input[type="file"][name="citizenship_photo"]')
        
        await input.trigger('change', {
            target: {
                files: [file]
            }
        })

        expect(wrapper.vm.form.citizenship_photo).toBe(file)
    })

    it('validates required fields before submission', async () => {
        await wrapper.find('form').trigger('submit')
        
        expect(wrapper.text()).toContain('Name is required')
        expect(wrapper.text()).toContain('Email is required')
        expect(wrapper.text()).toContain('Phone number is required')
    })

    it('submits form with all required fields and files', async () => {
        // Set form data
        await wrapper.setData({
            form: {
                name: 'Test Artisan',
                email: 'test@example.com',
                phone_number: '1234567890',
                basic_salary: 50000,
                pan_number: 'TEST123456',
                bank_account_number: '12345678901234',
                department_id: 1,
                status: 'active',
                skills: ['skill1', 'skill2'],
                profile_photo: new File(['test'], 'profile.jpg', { type: 'image/jpeg' }),
                citizenship_photo: new File(['test'], 'citizenship.jpg', { type: 'image/jpeg' })
            }
        })

        // Mock axios post
        axios.post.mockResolvedValueOnce({ 
            data: { 
                message: 'Artisan created successfully',
                artisan: {
                    id: 1,
                    name: 'Test Artisan'
                }
            }
        })

        // Submit form
        await wrapper.find('form').trigger('submit')

        // Verify axios was called with correct data
        expect(axios.post).toHaveBeenCalledWith('/api/artisans', expect.any(FormData))
        
        // Verify success message
        expect(wrapper.text()).toContain('Artisan created successfully')
    })
}) 