import * as React from 'react';

import { isNil } from 'ramda';
import { ParentSize } from '@visx/visx';

import { styled, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

<<<<<<< HEAD
=======
import {
  useSnackbar,
  useLocaleDateTimeFormat,
  copyToClipboard,
} from '@centreon/ui';

import {
  labelCopy,
  labelCommand,
  labelStatusInformation,
  labelDowntimeDuration,
  labelFrom,
  labelTo,
  labelAcknowledgedBy,
  labelAt,
  labelPerformanceData,
  labelCommandCopied,
  labelSomethingWentWrong,
} from '../../../translatedLabels';
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';
>>>>>>> 8db79d9fcba679033f2331b5b0eb08198aa0322c
import { ResourceDetails } from '../../models';

import SortableCards from './SortableCards';

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
<<<<<<< HEAD
=======
  const { t } = useTranslation();
  const { toDateTime } = useLocaleDateTimeFormat();
  const classes = useStyles();

  const { showSuccessMessage, showErrorMessage } = useSnackbar();

>>>>>>> 8db79d9fcba679033f2331b5b0eb08198aa0322c
  if (isNil(details)) {
    return <LoadingSkeleton />;
  }

<<<<<<< HEAD
=======
  const copyCommandLine = (): void => {
    try {
      copyToClipboard(details.command_line as string);

      showSuccessMessage(t(labelCommandCopied));
    } catch (_) {
      showErrorMessage(t(labelSomethingWentWrong));
    }
  };

>>>>>>> 8db79d9fcba679033f2331b5b0eb08198aa0322c
  return (
    <>
      <ParentSize>
        {({ width }): JSX.Element => (
          <div>
            <SortableCards details={details} panelWidth={width} />
          </div>
        )}
      </ParentSize>
    </>
  );
};

export default DetailsTab;
