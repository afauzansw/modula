export type CertificateListItem = {
    id: number;
    student: string;
    course: string;
    certificate_number: string;
    issued_at: string;
};

/** `{id, name}` option for the certificates filter dropdowns. */
export type CertificateFilterOption = {
    id: number;
    name: string;
};
