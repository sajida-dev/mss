import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    can?: Record<string, boolean>; // Add this line for permissions
}

export interface ExamType {
    id: number;
    name: string;
    code: string;
    is_final_term: boolean;
}

export interface Exam {
    id: number;
    title: string;
    academic_year: string;
    start_date: string;
    end_date: string;
    status: string; // 'scheduled' | 'in_progress' | 'completed' | 'cancelled'
    instructions?: string;
    exam_type_id: number;
    class_id: number;
    section_id?: number;
    school_id: number;
}

export interface ExamPaper {
    id: number;
    examId: number;
    paperId: number;
    examDate: string;
    startTime?: string;
    endTime?: string;
    totalMarks: number;
    passingMarks: number;
}

export interface SelectOptionById {
    id: string | number;
    name: string;
}

export interface SelectOptionByValue {
    value: string | number;
    label: string;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    permission?: string;
    matchRoutes?: string[];
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    phone_number: string;
    profile_photo_path?: string;
    profile_photo_url?: string;
    initials?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles?: Array<{ id: number; name: string; school_id?: number }>; // Added roles property
}

export type BreadcrumbItemType = BreadcrumbItem;
