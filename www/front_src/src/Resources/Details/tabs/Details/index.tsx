import * as React from 'react';

import { equals, isNil } from 'ramda';
import { Responsive } from '@visx/visx';
import { useAtomValue } from 'jotai/utils';

import { detailsAtom } from '../../detailsAtoms';
import DetailsLoadingSkeleton from '../../LoadingSkeleton';

import SortableCards from './SortableCards';

const DetailsTab = (): JSX.Element => {
  const details = useAtomValue(detailsAtom);

  return (
    <Responsive.ParentSize>
      {({ width }): JSX.Element => {
        const loading = isNil(details) || equals(width, 0);

        if (loading) {
          return <DetailsLoadingSkeleton />;
        }

        return (
          <div>
            <SortableCards details={details} panelWidth={width} />
          </div>
        );
      }}
    </Responsive.ParentSize>
  );
};

export default DetailsTab;
