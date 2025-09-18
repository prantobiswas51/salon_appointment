import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Timer, CalendarDays } from 'lucide-react';

import FullCalendar from "@fullcalendar/react";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import { RefreshCcw } from 'lucide-react';

import { useEffect, useState } from "react";
import axios from "axios";

type Appointment = {
    id: number;
    client_name: string;
    service: string;
    start_time: string;
    duration: number;
    status: string;
};

type Props = {
    appointments: Appointment[];
    inProgress: Appointment | null;
    nextAppointment: Appointment | null;
    countToday: number;
    countWeek: number;
    countMonth: number;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({
    appointments,
    inProgress,
    nextAppointment,
    countToday,
    countWeek,
    countMonth,
}: Props) {
    const [events, setEvents] = useState<any[]>([]);

    // ---- Load Calendar Events ----
    useEffect(() => {
        axios.get("/calendar/events").then((res) => {
            setEvents(res.data);
        });
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            {/* Current & Next Appointment */}
            <div className="flex flex-col md:grid md:grid-cols-2 dark:bg-gray-900 p-2 rounded-lg">
                {inProgress && (
                    <div className="w-auto border p-2 bg-sky-200/30 border-sky-500 m-2 rounded-md shadow-sm">
                        <h2 className='text-xl font-bold flex'><Timer className='mr-2' />Appointment in Progress</h2>
                        <h3 className='font-semibold flex'>{inProgress.client_name}</h3>
                        <h3>{inProgress.service}</h3>
                        <div className="flex justify-between items-center">
                            <h3>Time : {inProgress.start_time}</h3>
                            <span className='bg-green-200 px-2 p-1 rounded-2xl text-sm'>In progress</span>
                        </div>
                    </div>
                )}

                {nextAppointment && (
                    <div className="w-auto border p-2 bg-amber-200/30 border-amber-500 m-2 rounded-md shadow-sm">
                        <h2 className='text-xl font-bold flex'><Timer className='mr-2' />Next Appointment</h2>
                        <h3 className='font-semibold flex'>{nextAppointment.client_name}</h3>
                        <h3>{nextAppointment.service}</h3>
                        <div className="flex justify-between items-center">
                            <h3>Time : {nextAppointment.start_time}</h3>
                            <span className=' px-2 p-1 rounded-2xl text-sm'>Next</span>
                        </div>
                    </div>
                )}
            </div>

            {/* Stats */}
            <h2 className='text-xl font-bold flex mx-2 mt-6'>Today's Agenda</h2>
            <div className="w-auto px-4 md:grid md:grid-cols-3 gap-3 dark:bg-gray-900 p-2 rounded-lg">
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Today</div>
                    <span className='text-3xl flex'>{countToday} <div className="text-sm ml-2">appointments</div></span>
                </div>
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Week</div>
                    <span className='text-3xl flex'>{countWeek} <div className="text-sm ml-2">appointments</div></span>
                </div>
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Month</div>
                    <span className='text-3xl flex'>{countMonth} <div className="text-sm ml-2">appointments</div></span>
                </div>
            </div>

            {/* List of Appointments */}
            <h2 className='text-xl font-bold mx-2 mt-6 flex'><CalendarDays className='mr-2' />Today's Agenda</h2>
            <div className="w-auto mx-2 rounded-md">
                {appointments.map((app) => (
                    <div
                        key={app.id}
                        className="p-2 my-2 rounded-md border-green-600 border shadow-sm md:flex lg:max-w-[50%] md:justify-between md:items-center"
                    >
                        <div className="flex justify-between">
                            <div className="font-bold text-gray-600 flex items-center">
                                <Timer className='mr-1' />
                                {app.start_time}
                            </div>
                            <span
                                className={`p-2 py-1 rounded-2xl md:ml-4 ${app.status === 'completed'
                                    ? 'bg-green-600 text-white'
                                    : app.status === 'in_progress'
                                        ? 'bg-blue-400/60 text-black'
                                        : 'bg-pink-400/60 text-black'
                                    }`}
                            >
                                {app.status}
                            </span>
                        </div>
                        <div className="font-bold ml-2">{app.client_name}</div>
                        <div className="ml-2">{app.service}</div>
                    </div>
                ))}
            </div>

            {/* Calendar */}
            <div className="p-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-bold mb-4">Calendar</h2>
                    <button onClick={() => window.location.reload()}
                        className="px-3 py-1 bg-blue-500 flex  text-white rounded hover:bg-blue-600"
                    > <RefreshCcw className="mr-2 w-4"/> Sync</button>
                </div>
                <FullCalendar
                    plugins={[timeGridPlugin, interactionPlugin]}
                    initialView="timeGridWeek"
                    selectable={true}
                    events={events}
                    slotMinTime="09:00:00"
                    slotMaxTime="17:00:00"
                    height="auto"
                />
            </div>
        </AppLayout>
    );
}
