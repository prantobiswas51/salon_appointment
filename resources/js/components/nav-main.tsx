import React, { useState } from 'react';
import { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, ChevronRight } from 'lucide-react';

interface NavMainProps {
    items: NavItem[];
}

export function NavMain({ items }: NavMainProps) {
    const [openMenus, setOpenMenus] = useState<Record<string, boolean>>({});

    const toggleMenu = (key: string) => {
        setOpenMenus(prev => ({
            ...prev,
            [key]: !prev[key],
        }));
    };

    return (
        <nav className="space-y-1">
            {items.map((item) => {
                const isOpen = openMenus[item.href] || false;

                return (
                    <div key={item.href}>
                        <div
                            className="flex items-center justify-between px-4 py-2 hover:bg-gray-100 rounded cursor-pointer"
                            onClick={() => item.children ? toggleMenu(item.href) : null}
                        >
                            <Link
                                href={item.href}
                                className="flex items-center text-sm font-medium"
                            >
                                {item.icon && <item.icon className="mr-2 h-4 w-4" />}
                                {item.title}
                            </Link>

                            {item.children && (
                                isOpen ? <ChevronDown size={16} /> : <ChevronRight size={16} />
                            )}
                        </div>

                        {/* Collapsible children */}
                        {item.children && isOpen && (
                            <div className="ml-6 mt-1 space-y-1">
                                {item.children.map((child) => (
                                    <Link
                                        key={child.href}
                                        href={child.href}
                                        className="flex items-center px-3 py-1 text-sm text-gray-600 hover:text-black hover:bg-gray-100 rounded"
                                    >
                                        {child.icon && <child.icon className="mr-2 h-4 w-4" />}
                                        {child.title}
                                    </Link>
                                ))}
                            </div>
                        )}
                    </div>
                );
            })}
        </nav>
    );
}
