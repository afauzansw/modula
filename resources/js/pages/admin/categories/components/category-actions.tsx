import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { Button } from '@/components/ui/button';
import type { CategoryListItem } from '@/types';
import { CategoryFormDialog } from './category-form-dialog';

/** Edit + delete controls for one category row. */
export function CategoryActions({ category }: { category: CategoryListItem }) {
    return (
        <div className="flex justify-end gap-2">
            <CategoryFormDialog
                category={category}
                trigger={
                    <Button variant="outline" size="sm">
                        Edit
                    </Button>
                }
            />
            <ConfirmDialog
                trigger={
                    <Button variant="destructive" size="sm">
                        Delete
                    </Button>
                }
                title={`Delete "${category.name}"?`}
                description="Any courses in this category become uncategorized. This cannot be undone."
                form={CategoryController.destroy.form(category.id)}
                confirmLabel="Delete category"
            />
        </div>
    );
}
