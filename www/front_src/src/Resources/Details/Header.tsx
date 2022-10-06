import { useTranslation } from 'react-i18next';
import { hasPath, isNil, not, path, prop } from 'ramda';
import { useNavigate } from 'react-router-dom';

import {
  Grid,
  Typography,
  Theme,
  Link,
  Tooltip,
  Skeleton,
} from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import CopyIcon from '@mui/icons-material/FileCopy';
import SettingsIcon from '@mui/icons-material/Settings';
import LogsIcon from '@mui/icons-material/Assignment';
import ReportIcon from '@mui/icons-material/Assessment';
import { CreateCSSProperties } from '@mui/styles';
import Divider from '@mui/material/Divider';

import {
  StatusChip,
  SeverityCode,
  IconButton,
  useCopyToClipboard,
} from '@centreon/ui';

import {
  labelActionNotPermitted,
  labelConfigure,
  labelCopyLink,
  labelLinkCopied,
  labelViewLogs,
  labelViewReport,
  labelSomethingWentWrong,
} from '../translatedLabels';
import { ResourceUris } from '../models';
import { replaceBasename } from '../helpers';

import { ResourceDetails } from './models';
import SelectableResourceName from './tabs/Details/SelectableResourceName';

import { DetailsSectionProps } from '.';

interface MakeStylesProps {
  displaySeverity: boolean;
}

const useStyles = makeStyles<Theme, MakeStylesProps>((theme) => ({
  containerIcons: {
    alignItems: 'center',
    display: 'flex',
  },
  divider: {
    borderColor: theme.palette.text.secondary,
    margin: theme.spacing(1, 0.5),
  },
  header: ({ displaySeverity }): CreateCSSProperties<MakeStylesProps> => ({
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: `${
      displaySeverity ? 'auto' : ''
    } auto minmax(0, 1fr) auto`,
    height: 43,
    padding: theme.spacing(0, 2.5, 0, 1),
  }),
  parent: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto minmax(0, 1fr)',
  },
  report: {
    marginLeft: theme.spacing(0.5),
  },
  resourceName: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: 'minmax(auto, min-content) min-content',
    height: '100%',
  },
  resourceNameConfigurationIcon: {
    alignSelf: 'center',
    display: 'flex',
    minWidth: theme.spacing(2.5),
  },
  resourceNameConfigurationLink: {
    height: theme.spacing(2.5),
  },
  resourceNameContainer: {
    display: 'flex',
    flexDirection: 'column',
    height: '100%',
    width: '100%',
  },
  resourceNameTooltip: {
    maxWidth: 'none',
  },
  truncated: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container item alignItems="center" spacing={2} style={{ flexGrow: 1 }}>
    <Grid item>
      <Skeleton height={25} variant="circular" width={25} />
    </Grid>
    <Grid item>
      <Skeleton height={25} width={250} />
    </Grid>
  </Grid>
);

type Props = {
  onSelectParent: (resource: ResourceDetails) => void;
} & DetailsSectionProps;

const Header = ({ details, onSelectParent }: Props): JSX.Element => {
  const classes = useStyles({
    displaySeverity: not(isNil(details?.severity)),
  });
  const { t } = useTranslation();
  const navigate = useNavigate();

  const { copy } = useCopyToClipboard({
    errorMessage: t(labelSomethingWentWrong),
    successMessage: t(labelLinkCopied),
  });

  const copyLink = (): Promise<void> => copy(window.location.href);
  const selectResourceDetails = (): void =>
    onSelectParent(details as ResourceDetails);

  const navigateToResourceUris = (
    category: keyof ResourceUris,
  ): (() => void) => {
    return (): void => {
      const url = replaceBasename({
        endpoint: prop(category, resourceUris) || '',
        newWord: '/',
      });

      navigate(`${url}`);
    };
  };

  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  const resourceUris = path<ResourceUris>(
    ['links', 'uris'],
    details,
  ) as ResourceUris;

  const resourceConfigurationUri = prop('configuration', resourceUris);

  const resourceConfigurationUriTitle = isNil(resourceConfigurationUri)
    ? t(labelActionNotPermitted)
    : '';

  const resourceConfigurationIconColor = isNil(resourceConfigurationUri)
    ? 'disabled'
    : 'primary';

  return (
    <div className={classes.header}>
      {details.severity && (
        <img
          alt="severity"
          height={24}
          src={details?.severity?.icon?.url}
          width={24}
        />
      )}
      <StatusChip
        label={t(details.status.name)}
        severityCode={details.status.severity_code}
      />
      <div className={classes.resourceNameContainer}>
        <div
          aria-label={`${details.name}_hover`}
          className={classes.resourceName}
        >
          <Tooltip
            classes={{ tooltip: classes.resourceNameTooltip }}
            placement="top"
            title={details.name}
          >
            <Typography className={classes.truncated}>
              {details.name}
            </Typography>
          </Tooltip>
          <Tooltip title={resourceConfigurationUriTitle}>
            <div className={classes.resourceNameConfigurationIcon}>
              <Link
                aria-label={`${t(labelConfigure)}_${details.name}`}
                className={classes.resourceNameConfigurationLink}
                data-testid={labelConfigure}
                href={resourceConfigurationUri}
              >
                <SettingsIcon
                  color={resourceConfigurationIconColor}
                  fontSize="small"
                />
              </Link>
            </div>
          </Tooltip>
        </div>
        {hasPath(['parent', 'status'], details) && (
          <div className={classes.parent}>
            <StatusChip
              severityCode={
                details.parent.status?.severity_code || SeverityCode.None
              }
              size="small"
            />
            <SelectableResourceName
              name={details.parent.name}
              variant="caption"
              onSelect={selectResourceDetails}
            />
          </div>
        )}
      </div>
      <div className={classes.containerIcons}>
        <IconButton
          ariaLabel={t(labelViewLogs)}
          data-testid={labelViewLogs}
          size="small"
          title={t(labelViewLogs)}
          onClick={navigateToResourceUris('logs')}
        >
          <LogsIcon fontSize="small" />
        </IconButton>
        <IconButton
          ariaLabel={t(labelViewReport)}
          className={classes.report}
          data-testid={labelViewReport}
          size="small"
          title={t(labelViewReport)}
          onClick={navigateToResourceUris('reporting')}
        >
          <ReportIcon fontSize="small" />
        </IconButton>
        <Divider flexItem className={classes.divider} orientation="vertical" />
        <IconButton
          ariaLabel={t(labelCopyLink)}
          data-testid={labelCopyLink}
          size="small"
          title={t(labelCopyLink)}
          onClick={copyLink}
        >
          <CopyIcon fontSize="small" />
        </IconButton>
      </div>
    </div>
  );
};

export default Header;
