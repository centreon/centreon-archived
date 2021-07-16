import * as React from 'react';

import { isNil, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';
import { ParentSize } from '@visx/visx';

import { Grid, styled, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { ResourceDetails } from '../../models';

import DetailsCard from './DetailsCard';
import getDetailCardLines from './DetailsCard/cards';

const useStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
    gridTemplateRows: '67px',
  },
}));

const CardSkeleton = styled(Skeleton)(() => ({
  transform: 'none',
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.loadingSkeleton}>
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
    </div>
  );
};

interface Props {
  details?: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
  const { t } = useTranslation();
  const { toDateTime } = useLocaleDateTimeFormat();

  if (isNil(details)) {
    return <LoadingSkeleton />;
  }

  return (
    <ParentSize>
      {({ width }): JSX.Element => (
        <Grid container spacing={1}>
          {getDetailCardLines({ details, t, toDateTime }).map(
            ({ title, field, xs = 6, line, active, isCustomCard }) => {
              const variableXs = (width > 600 ? xs / 2 : xs) as 3 | 6 | 12;
              const displayCard = !isNil(field) && !isEmpty(field);

              return (
                displayCard && (
                  <Grid item key={title} xs={variableXs}>
                    <DetailsCard
                      active={active}
                      isCustomCard={isCustomCard}
                      line={line}
                      title={t(title)}
                    />
                  </Grid>
                )
              );
            },
          )}
        </Grid>
      )}
    </ParentSize>
  );
};

export default DetailsTab;
