import * as React from 'react';

import { path, isNil, equals, last, pipe, not } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { Resource } from '../../../models';
import ExportableGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import { MousePosition } from '../../../Graph/Performance/Graph/mouseTimeValueAtoms';

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
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
    )}px, auto))`,
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
              limitLegendRows
              graphHeight={120}
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
