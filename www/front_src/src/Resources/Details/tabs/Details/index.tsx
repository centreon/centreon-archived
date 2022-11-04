import { equals, isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useTheme } from '@mui/material';

import { detailsAtom, panelWidthStorageAtom } from '../../detailsAtoms';
import DetailsLoadingSkeleton from '../../LoadingSkeleton';

import SortableCards from './SortableCards';

const DetailsTab = (): JSX.Element => {
  const theme = useTheme();
  const details = useAtomValue(detailsAtom);
  const panelWidth = useAtomValue(panelWidthStorageAtom);
  const loading = isNil(details) || equals(panelWidth, 0);
  const panelPadding = parseInt(theme.spacing(4), 10);

  return loading ? (
    <DetailsLoadingSkeleton />
  ) : (
    <div>
      <SortableCards details={details} panelWidth={panelWidth - panelPadding} />
    </div>
  );
};

export default DetailsTab;
