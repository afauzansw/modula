import { User } from "./auth";
import { TCourse } from "./course";

export type TCertificate = {
    id: number;
    user_id: number;
    course_id: number;
    certificate_number: string;
    issued_at: string;
    created_at: string;
    updated_at: string;

    // Relations
    user: User;
    course: TCourse;
    
    // Included fields

    // Appended fields
    image?: string;
};

/** `{id, name}` option for the certificates filter dropdowns. */
export type CertificateFilterOption = {
    id?: number;
    name?: string;
};
