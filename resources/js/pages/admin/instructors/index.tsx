import InstructorController from '@/actions/App/Http/Controllers/Admin/InstructorController';
import { UserDirectoryTable } from '@/components/user-directory-table';
import { index } from '@/routes/admin/instructors';

export default function InstructorsIndex() {
    return (
        <UserDirectoryTable
            title="Instructor"
            description="Instructors on the platform"
            noun="instructor"
            fetchUrl={InstructorController.fetch.url()}
            bulkStatusForm={InstructorController.bulkUpdateStatus.form()}
        />
    );
}

InstructorsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Instructor',
            href: index(),
        },
    ],
};
