<?php
include('includes/header.php');
if(!isset($_SESSION['login'])){ $model->redirect('login.php');}
$agentOptions = $model->getAgentRoster();
$selectedAgent = isset($_POST['agent']) ? $_POST['agent'] : '';
$descriptionValue = isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : '';
$dateStartValue = isset($_POST['date']) ? htmlspecialchars($_POST['date'], ENT_QUOTES, 'UTF-8') : '';
$dateEndValue = isset($_POST['enddate']) ? htmlspecialchars($_POST['enddate'], ENT_QUOTES, 'UTF-8') : '';
$otherPartyValue = isset($_POST['other_party']) ? htmlspecialchars($_POST['other_party'], ENT_QUOTES, 'UTF-8') : '';
$serviceGroupValue = isset($_POST['service_group']) ? htmlspecialchars($_POST['service_group'], ENT_QUOTES, 'UTF-8') : '';
$callIdValue = isset($_POST['call_id']) ? htmlspecialchars($_POST['call_id'], ENT_QUOTES, 'UTF-8') : '';
?>
<link rel="stylesheet" href="ui/1.11.2/themes/base/jquery-ui.css">
<script src="jquery-1.10.2.js"></script>
<script src="ui/1.11.2/jquery-ui.js"></script>
<script>
$(function() {
        $('#search-date-start').datepicker({dateFormat:'yy-mm-dd'});
        $('#search-date-end').datepicker({dateFormat:'yy-mm-dd'});
});
</script>
<div class="outerlayer">
  <div class="outerlayer1">
    <section class="search-panel">
      <header class="search-panel__header">
        <div>
          <h2 class="search-panel__title">Find recordings</h2>
          <p class="search-panel__subtitle">Filter by agent, participants, or time frame to retrieve conversations outside of the recent dashboard view.</p>
        </div>
        <a class="search-panel__link" href="index.php">Return to dashboard</a>
      </header>
      <form action="index.php" method="POST" class="search-form">
        <div class="search-form__grid">
          <label class="search-field">
            <span class="search-field__label">Agent</span>
            <select name="agent" id="agent-select" class="search-field__control">
              <option value="">All agents</option>
              <?php foreach ($agentOptions as $option) {
                        $directoryValue = htmlspecialchars($option['directory'], ENT_QUOTES, 'UTF-8');
                        $labelValue = htmlspecialchars($option['displayName'], ENT_QUOTES, 'UTF-8');
                        $isSelected = ($selectedAgent !== '' && $selectedAgent === $option['directory']) ? ' selected' : '';
              ?>
              <option value="<?php echo $directoryValue; ?>"<?php echo $isSelected; ?>><?php echo $labelValue; ?></option>
              <?php } ?>
            </select>
          </label>
          <label class="search-field">
            <span class="search-field__label">Description</span>
            <input type="text" name="name" class="search-field__control" value="<?php echo $descriptionValue; ?>" placeholder="Caller or subject" />
          </label>
          <label class="search-field">
            <span class="search-field__label">Date start</span>
            <input type="text" name="date" id="search-date-start" class="search-field__control" value="<?php echo $dateStartValue; ?>" placeholder="YYYY-MM-DD" autocomplete="off" />
          </label>
          <label class="search-field">
            <span class="search-field__label">Date end</span>
            <input type="text" name="enddate" id="search-date-end" class="search-field__control" value="<?php echo $dateEndValue; ?>" placeholder="YYYY-MM-DD" autocomplete="off" />
          </label>
          <label class="search-field">
            <span class="search-field__label">Other party</span>
            <input type="text" name="other_party" class="search-field__control" value="<?php echo $otherPartyValue; ?>" placeholder="Phone number or contact" />
          </label>
          <label class="search-field">
            <span class="search-field__label">Service group</span>
            <input type="text" name="service_group" class="search-field__control" value="<?php echo $serviceGroupValue; ?>" placeholder="Queue or team" />
          </label>
          <label class="search-field">
            <span class="search-field__label">Call ID</span>
            <input type="text" name="call_id" class="search-field__control" value="<?php echo $callIdValue; ?>" placeholder="ID" />
          </label>
        </div>
        <div class="search-form__actions">
          <input type="hidden" name="action" value="search">
          <button type="submit" class="search-form__submit">Search</button>
          <a class="search-form__reset" href="search.php">Clear filters</a>
        </div>
        <p class="search-panel__hint">Use the dashboard for the latest 14 days of recordings. Searches here retrieve older history and large archives.</p>
      </form>
    </section>
  </div>
</div>
<?php include('includes/footer.php'); ?>
