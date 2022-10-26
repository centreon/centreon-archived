import { useAtomValue } from 'jotai/utils';
import { lte, isNil } from 'ramda';

import {
  panelWidthStorageAtom,
  selectedResourcesDetailsAtom,
} from '../../Details/detailsAtoms';

interface UseMediaQueryListing {
  applyBreakPoint: boolean;
}

const useMediaQueryListing = (): UseMediaQueryListing => {
  const panelWidth = useAtomValue(panelWidthStorageAtom);
  const selectedResourceDetails = useAtomValue(selectedResourcesDetailsAtom);
  const isPanelOpen = !isNil(selectedResourceDetails?.resourceId);

  const newWidth = window.innerWidth - panelWidth;
  const width = isPanelOpen ? newWidth : window.innerWidth;
  const widthToApplyBreakPoint = 1100;

  return { applyBreakPoint: lte(width, widthToApplyBreakPoint) };
};

export default useMediaQueryListing;
