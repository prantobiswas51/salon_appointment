import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import WeekCalendar from "@/components/WeekCalendar";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'New Appointment',
        href: '/appointment',
    },
];

function FlashMessage({ type, message }: { type: 'success' | 'error'; message: string }) {
    const bg = type === 'success'
        ? 'bg-green-100 border-green-300 text-green-800'
        : 'bg-red-100 border-red-300 text-red-800';

    return (
        <div className={`mb-4 p-3 border ${bg} rounded`}>
            {message}
        </div>
    );
}

export default function CreateAppointment() {
    const [activeTab, setActiveTab] = React.useState<'existing' | 'new'>('new');

    const page = usePage();
    const flash = page.props?.flash || {};

    const { data, setData, post, processing, errors } = useForm({
        client_number: '',
        email: '',
        new_client_name: '',
        new_client_phone: '',
        service: '',
        appointment_time: '',
        status: 'Scheduled',
        notes: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/appointment/store', {
            onSuccess: () => {
                console.log("Appointment created successfully!");
            },
            onError: (errors) => {
                console.error("Validation errors:", errors);
            },
            onFinish: () => {
                console.log("Request finished.");
            }
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Appointment" />
            <div className="flex">
                <div className='flex items-center h-full'>
                    <div className="p-10 mx-auto py-16 bg-pink-50 w-2xl border rounded-2xl shadow-md">
                        <h1 className="text-xl font-bold mb-6 bg-pink-500 p-2 rounded-md text-white">Create New Appointment</h1>

                        {/* Tabs */}
                        <div className="flex space-x-4 mb-6">
                            <button
                                type="button"
                                onClick={() => {
                                    setActiveTab('new');
                                    setData({
                                        client_number: '',
                                        email: '',
                                        new_client_name: '',
                                        new_client_phone: '',
                                        service: '',
                                        appointment_time: '',
                                        status: 'Scheduled',
                                        notes: '',
                                    });
                                }}
                                className={`px-4 py-2 rounded ${activeTab === 'new' ? 'bg-pink-600 text-white' : 'bg-gray-200'}`}
                            >
                                New Client
                            </button>

                            <button
                                type="button"
                                onClick={() => {
                                    setActiveTab('existing');
                                    setData({
                                        client_number: '',
                                        email: '',
                                        new_client_name: '',
                                        new_client_phone: '',
                                        service: '',
                                        appointment_time: '',
                                        status: '',
                                        notes: '',
                                    });
                                }}
                                className={`px-4 py-2 rounded ${activeTab === 'existing' ? 'bg-pink-600 text-white' : 'bg-gray-200'}`}
                            >
                                Existing Client
                            </button>

                        </div>

                        {flash.success && <FlashMessage type="success" message={flash.success} />}
                        {flash.error && <FlashMessage type="error" message={flash.error} />}

                        {/* Form */}
                        <form onSubmit={submit} className="space-y-4 p-2">
                            {/* Client Fields */}
                            {activeTab === 'existing' ? (
                                <div>
                                    <label className="block mb-1">Client Number</label>
                                    <input
                                        type="text"
                                        placeholder='e.g. +1 848 648 8448'
                                        className="w-full border p-2 rounded"
                                        value={data.client_number}
                                        onChange={(e) => setData('client_number', e.target.value)}
                                    />
                                    {errors.client_number && <p className="text-red-500">{errors.client_number}</p>}
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block mb-1">Client Name</label>
                                        <input
                                            type="text"
                                            className="w-full border p-2 rounded"
                                            placeholder='e.g. John'
                                            value={data.new_client_name}
                                            onChange={(e) => setData('new_client_name', e.target.value)}
                                        />
                                        {errors.new_client_name && <p className="text-red-500">{errors.new_client_name}</p>}
                                    </div>
                                    <div>
                                        <label className="block mb-1">Client Phone</label>
                                        <input
                                            type="number"
                                            className="w-full border p-2 rounded"
                                            placeholder='e.g. +2 485 485 744'
                                            value={data.new_client_phone}
                                            onChange={(e) => setData('new_client_phone', e.target.value)}
                                        />
                                        {errors.new_client_phone && <p className="text-red-500">{errors.new_client_phone}</p>}
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block mb-1">Email</label>
                                        <input
                                            type="email"
                                            className="w-full border p-2 rounded"
                                            placeholder="clientmail@domain.com"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                        />
                                        {errors.email && <p className="text-red-500">{errors.email}</p>}
                                    </div>
                                </div>
                            )}

                            {/* Service */}
                            <div>
                                <label className="block mb-1">Service</label>
                                <select
                                    value={data.service}
                                    onChange={(e) => setData('service', e.target.value)}
                                    className="w-full border p-2 rounded"
                                >
                                    <option value="">Select</option>
                                    <option value="Hair Cut">Hair Cut</option>
                                    <option value="Beard Shaping">Beard Shaping</option>
                                    <option value="Other Services">Other Services</option>
                                </select>
                                {errors.service && <p className="text-red-500">{errors.service}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block mb-1">Appointment Time</label>
                                    <input
                                        type="datetime-local"
                                        className="w-full border p-2 rounded"
                                        value={data.appointment_time}
                                        onChange={(e) => setData('appointment_time', e.target.value)}
                                    />
                                    {errors.appointment_time && <p className="text-red-500">{errors.appointment_time}</p>}
                                </div>

                                {/* Status */}
                                <div>
                                    <label className="block mb-1">Status</label>
                                    <select
                                        value={data.status}
                                        onChange={(e) => setData('status', e.target.value)}
                                        className="w-full border p-2 rounded"
                                    >
                                        <option value="Scheduled">Scheduled</option>
                                        <option value="Confirmed">Confirmed</option>
                                        <option value="Canceled">Canceled</option>
                                    </select>
                                    {errors.status && <p className="text-red-500">{errors.status}</p>}
                                </div>
                            </div>

                            {/* Notes */}
                            <div className="mb-4">
                                <label htmlFor="notes" className="block mb-1 font-medium text-gray-700">
                                    Notes
                                </label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-pink-200"
                                ></textarea>
                                {errors.notes && <p className="mt-1 text-sm text-red-600">{errors.notes}</p>}
                            </div>

                            {/* Submit Button */}
                            <div>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-4 py-2 bg-pink-600 text-white rounded"
                                >
                                    Save Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div className="p-2 w-full h-screen">
                    <WeekCalendar
                        value={data.appointment_time}
                        onChange={(val) => setData("appointment_time", val)}
                        weekStartsOn={0} // 0=Sunday, 1=Monday
                        slotMinutes={30}
                    />
                </div>

            </div>
        </AppLayout>
    );
}
