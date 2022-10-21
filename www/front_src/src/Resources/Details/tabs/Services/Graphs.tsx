import { RefObject } from 'react';

import { equals, isNil, last, not, path, pipe } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import ExportableGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import { MousePosition } from '../../../Graph/Performance/Graph/mouseTimeValueAtoms';
import { Resource } from '../../../models';

interface Props {
  infiniteScrollTriggerRef: RefObject<HTMLDivElement>;
  services: Array<Resource>;
}

export interface ResourceGraphMousePosition {
  mousePosition: MousePosition;
  resourceId: string | number;
}

const useStyles = makeStyles((theme) => ({
  graph: {
    columnGap: '8px',
    display: 'grid',
    gridTemplateColumns: `repeat(auto-fill, minmax(${theme.spacing(
      40,
    )}, auto))`,
    rowGap: '8px',
  },
}));

const ServiceGraphs = ({
  services,
  infiniteScrollTriggerRef,
}: Props): JSX.Element => {
  const classes = useStyles();

  const servicesWithGraph = services.filter(
    pipe(path(['links', 'endpoints', 'performance_graph']), isNil, not),
  );

  return (
    <div className={classes.graph}>
      {servicesWithGraph.map((service) => {
        const { id } = service;
        const isLastService = equals(last(servicesWithGraph), service);

        return (
          <div key={id}>
            <ExportableGraphWithTimeline
              interactWithGraph
              limitLegendRows
              graphHeight={120}
              isRenderAdditionalGraphActions={false}
              resource={service}
            />
            {isLastService && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </div>
  );
};

export default ServiceGraphs;
