import { ref, computed } from 'vue';

/**
 * Shared bar chart interaction composable.
 * Isolates hover previews to mouse pointers (`pointerType === 'mouse'`) so
 * mobile touch devices rely cleanly on tap (`toggleSelect`) without stuck hover states.
 */
export function useChartSelection(getKey = (item) => item?.key ?? item?.date) {
    const hoveredItem = ref(null);
    const selectedItem = ref(null);

    const activeItem = computed(() => hoveredItem.value || selectedItem.value);

    const isSelected = (item) => {
        if (!selectedItem.value || !item) return false;
        return getKey(selectedItem.value) === getKey(item);
    };

    const isActive = (item) => {
        if (!activeItem.value || !item) return false;
        return getKey(activeItem.value) === getKey(item);
    };

    const onPointerEnter = (event, item) => {
        if (event?.pointerType === 'mouse') {
            hoveredItem.value = item;
        }
    };

    const onPointerLeave = (event) => {
        if (event?.pointerType === 'mouse') {
            hoveredItem.value = null;
        }
    };

    const toggleSelect = (item) => {
        if (isSelected(item)) {
            selectedItem.value = null;
            hoveredItem.value = null;
        } else {
            selectedItem.value = item;
        }
    };

    const clearSelection = () => {
        selectedItem.value = null;
        hoveredItem.value = null;
    };

    return {
        hoveredItem,
        selectedItem,
        activeItem,
        isSelected,
        isActive,
        onPointerEnter,
        onPointerLeave,
        toggleSelect,
        clearSelection,
    };
}

